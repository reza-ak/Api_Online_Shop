<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BrandResource;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class BrandController extends ApiController
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
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
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
