<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BrandResource;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class BrandController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::paginate(4);
        return $this->successResponse([
            'brands' => BrandResource::collection($brands),
            'links' => BrandResource::collection($brands)->response()->getData()->links,
            'meta' => BrandResource::collection($brands)->response()->getData()->meta,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required|unique:brands',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        // زمانی که نیاز هست مثلا 2 یا بیشتر تغییرات در جدول داشته باشیم میتوانیم از transaction در mysql استفاده کنیم برای مثال مثلا اطلاعات در جدول order ذخیره شده و در جدول order_item هم ذخیره شده ولی وقتی اطلاعات میخواهد در جدول transaction ذخیره شود به مشکل میخورد و این اتفاق نمی افتد پس نباید دو جدول قبلی هم ذخیره سازی انجام میشد در این مواقع میتوان از transactoin در mysql استفاده کرد به صورت زیر
        try {
            DB::beginTransaction(); // شروع transaction

            $brand = Brand::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
            ]);

            DB::commit(); // در صورت موفقیت تغییرات انجام میشود\

        } catch (Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($th->getMessage(), 500);
        }
        return $this->successResponse(new BrandResource($brand), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::find($id);
        if (!is_null($brand)) {
            return $this->successResponse(new BrandResource($brand), 200);
        } else {
            return $this->errorResponse("Not Found", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            'display_name' => 'unique:brands',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        try {
            DB::beginTransaction(); // شروع transaction

            $brand->update([
                'name' => $request->has('name') ? $request->name : $brand->name,
                'display_name' => $request->has('display_name') ? $request->display_name : $brand->display_name,
            ]);

            DB::commit(); // در صورت موفقیت تغییرات انجام میشود\

        } catch (Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($th->getMessage(), 500);
        }
        return $this->successResponse(new BrandResource($brand), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return $this->successResponse(new BrandResource($brand), 200);
    }

    // get the products of any brand
    public function products(string $id)
    {
        $brand = Brand::find($id);
        return $this->successResponse(new BrandResource($brand->load('products')), 200);
    }
}
