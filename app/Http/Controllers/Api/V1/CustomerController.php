<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetCustomersRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCustomersRequest $request)
    {
        $customers = Customer::search($request->search)->latest()->paginate($request->limit) ?? 10;

        return ApiResponses::success(
            new PaginationResource($customers, CustomerResource::class),
            'Customers list'
        );
    }

    public function option(GetCustomersRequest $request)
    {
        $customers = Customer::select('id', 'name')
            ->search($request->search)
            ->orderBy('name')
            ->get();


        return ApiResponses::success(
            CustomerResource::collection($customers),
            'Customers list'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return ApiResponses::success(
            new CustomerResource($customer),
            'Customer created',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponses::error(
                "Customer Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponses::success(
            new CustomerResource($customer),
            "Customer Detail"
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponses::error(
                "Customer Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->update($request->validated());

        return ApiResponses::success(
            new CustomerResource($customer),
            "Customer Update Successfully"
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponses::error(
                "Customer Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->delete();
        return ApiResponses::success(
            null,
            "Customer deleted successfully"
        );
    }
}
