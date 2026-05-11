<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductsRequest $request)
    {
        $products = Product::with('category')->search($request->search)->latest()->paginate($request->limit) ?? 10;

        return ApiResponses::success(
            new PaginationResource($products, ProductResource::class),
            'Products list'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return ApiResponses::success(
            new ProductResource($product->load('category')),
            'Product created',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return ApiResponses::error(
                "Product Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponses::success(
            new ProductResource($product),
            "Product Detail"
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponses::error(
                "Product Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        $product->update($request->validated());

        return ApiResponses::success(
            new ProductResource($product->load('category')),
            "Product Update Successfully"
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponses::error(
                "Product Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return ApiResponses::success(
            null,
            "Product deleted successfully"
        );
    }
}
