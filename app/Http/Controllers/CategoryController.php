<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use Throwable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::paginate(4);
        return $this->successResponse([
            'brands' => CategoryResource::collection($categories),
            'links' => CategoryResource::collection($categories)->response()->getData()->links,
            'meta' => CategoryResource::collection($categories)->response()->getData()->meta,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|integer',
            'name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        try {
            DB::beginTransaction(); // شروع transaction

            $category = Category::create([
                'parent_id' => $request->parent_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit(); // در صورت موفقیت تغییرات انجام میشود\

        } catch (Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($th->getMessage(), 500);
        }
        return $this->successResponse(new CategoryResource($category), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if(!is_null($category)){
            return $this->successResponse(new CategoryResource($category), 201);
        }else{
            return $this->errorResponse("Not Found", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'integer',
            'name' => 'string',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        try {
            DB::beginTransaction(); // شروع transaction

            $category->update([
                'parent_id' => $request->has('parent_id') ? $request->parent_id : $category->parent_id,
                'name' => $request->has('name') ? $request->name : $category->name,
                'description' => $request->has('description') ? $request->description : $category->description,
            ]);

            DB::commit(); // در صورت موفقیت تغییرات انجام میشود\

        } catch (Throwable $th) {
            DB::rollBack();
            return $this->errorResponse($th->getMessage(), 500);
        }
        return $this->successResponse(new CategoryResource($category), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->successResponse(new CategoryResource($category), 200);
    }
}
