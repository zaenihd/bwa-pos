<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetProductCategoriesRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductCategoriesRequest $request)
    {
        $categories = ProductCategory::search($request->search)->latest()->paginate($request->limit) ?? 10;

        return ApiResponses::success(
            new PaginationResource($categories, ProductCategoryResource::class),
            'Product categories list'
        );
    }

    public function option(GetProductCategoriesRequest $request)
    {
        $categories = ProductCategory::select('id', 'name')
            ->search($request->search)
            ->orderBy('name')
            ->get();


        return ApiResponses::success(
            ProductCategoryResource::collection($categories),
            'Product categories list'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());

        return ApiResponses::success(
            new ProductCategoryResource($category),
            'Product category created',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponses::error(
                "Product Category Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponses::success(
            new ProductCategoryResource($category),
            "Product Category Detail"
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponses::error(
                "Product Category Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        $category->update($request->validated());

        return ApiResponses::success(
            new ProductCategoryResource($category),
            "Product Category Update Successfully"
        );
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponses::error(
                "Product Category Not Found",
                Response::HTTP_NOT_FOUND
            );
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return ApiResponses::success(
            null,
            "Product category deleted successfully"
        );
    }
}
