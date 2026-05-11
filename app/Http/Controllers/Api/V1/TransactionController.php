<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetTransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetTransactionsRequest $request)
    {
        $transactions = Transaction::with(['customer'])
            ->search($request->search)
            ->latest()
            ->paginate($request->limit ?? 10);

        return ApiResponses::success(
            new PaginationResource($transactions, TransactionResource::class),
            'Transactions list'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            $subtotal = 0;
            $itemsData = [];

            // Group requested items by product_id to sum quantities if duplicate product_id provided
            $requestedItems = [];
            foreach ($request->items as $item) {
                if (isset($requestedItems[$item['product_id']])) {
                    $requestedItems[$item['product_id']] += $item['quantity'];
                } else {
                    $requestedItems[$item['product_id']] = $item['quantity'];
                }
            }

            foreach ($requestedItems as $productId => $quantity) {
                // Lock the product row for update to prevent race conditions on stock
                $product = Product::lockForUpdate()->find($productId);

                if (!$product) {
                    throw new \Exception("Product with ID {$productId} not found");
                }

                if ($product->stock < $quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}");
                }

                $itemSubtotal = $product->price * $quantity;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal
                ];

                // Deduct stock
                $product->stock -= $quantity;
                $product->save();
            }

            // Calculate tax (11%)
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            // Generate unique transaction code
            $code = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create Transaction
            $transaction = Transaction::create([
                'code' => $code,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            // Create Transaction Items
            foreach ($itemsData as $itemData) {
                $transaction->items()->create($itemData);
            }

            DB::commit();

            return ApiResponses::success(
                new TransactionResource($transaction->load(['customer', 'items.product'])),
                'Transaction created',
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponses::error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'items.product'])->find($id);

        if (!$transaction) {
            return ApiResponses::error(
                "Transaction Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponses::success(
            new TransactionResource($transaction),
            "Transaction Detail"
        );
    }
}
