<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProductCategoryImageRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductCategoryImageController extends Controller
{
    public function store(UploadProductCategoryImageRequest $request, string $id){
        $category = ProductCategory::find($id);

        if(!$category){
            return ApiResponses::error("Category not found", Response::HTTP_NOT_FOUND);

        }

        if($category -> image){
            Storage::disk('public')->delete($category -> image);
        }

        $path = $request->file("image")->store('product_categories', 'public');

        $category->update(['image' => $path]);

        return ApiResponses::success(
            new ProductCategoryResource($category),
            "Image uploaded successfully"

        );





    }
}
