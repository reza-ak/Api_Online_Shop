<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
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
            'brand_id' => 'required',
            'category_id' => 'required',
            'primary_image' => 'required|image',
            'price' => 'integer',
            'quantity' => 'integer',
            'delivery_amount' => 'nullable|integer',
            'description' => 'required',
            'image.*' => 'nullable|image', // به ازای همه تصاویری که به صورت یک آرایه دریافت میشود اعتبار سنجی را روی همه آن ها اعمال میکند
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        try {
            DB::beginTransaction();

            $primaryImageName = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
            $request->primary_image->storeAs('images/products', $primaryImageName, 'public'); // (config/filesystems.php)مسیر /storage/app/public/images/products (php artisan storage:link)
            // با دستور بالا ممکن است در بعضی از هاست های اشتراکی مشکل ایجاد شود و storage داخل public لینک نشود پس میتوان به در فایل config/filesystems.php  در قسمت public دستور زیر را تغییر داد تا به جای ذخیره در storage مستقیما در public ذخیره شود.
            // 'root' => storage_path('app/public'),
            // تبدیل بشه به
            // 'root' => public_path() . '/storage',

            if ($request->has('images')) {
                $fileNameImages = [];
                foreach ($request->images as $image) {
                    $fileImageName = Carbon::now()->microsecond . '.' . $image->extension();
                    $image->storeAs('images/products', $fileImageName, 'public');
                    array_push($fileNameImages, $fileImageName);
                }
            }

            $product = Product::create([
                'name' => $request->name,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'primary_image' => $primaryImageName,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'delivery_amount' => $request->delivery_amount,
                'description' => $request->description,
            ]);

            if ($request->has('images')) {
                foreach ($fileNameImages as $imageName) {
                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $imageName
                    ]);
                }
            }
            DB::commit();

        } catch (Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($th->getMessage(), 500);
        }
        return $this->successResponse(new ProductResource($product), 201);
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
