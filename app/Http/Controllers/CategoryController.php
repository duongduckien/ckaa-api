<?php

namespace App\Http\Controllers;

use Api\Repositories\CategoryRepository;
use App\Api\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Http\Controllers\ApiController;
use Api\Transformers\CategoryTransformer;

class CategoryController extends ApiController
{

    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        parent::__construct();
    }

    public function create(CategoryRequest $request)
    {

        $data = [
            "name" => $request->get('name')
        ];

        $result = $this->categoryRepository->createCategory($data);

        if ($result) {
            return $this->respondCreated([
                'id' => $result->id,
                'name' => $result->name
            ]);
        }

        return $this->respondInternalError('An error ocurred while creating category');

    }

    public function remove($id)
    {

        if ($this->categoryRepository->getCategoryById($id)) {

            try {
                $this->categoryRepository->removeCategory($id);
                return $this->respondSuccess('Category deleted successfully!');
            }
            catch (\Exception $e) {
                return $this->respondNotFound("Something went wrong!");
            }

        }

        return $this->respondNotFound("Category doesn't exist!");

    }

    public function getAll()
    {

        $categories = $this->categoryRepository->getCategories();

        if (!$categories) {
            return $this->respondNotFound('The category was not found');
        }

        $response = $this->processCollection($categories, new CategoryTransformer(), 'categories');

        return $response;

    }

    public function get($id)
    {

        $category = $this->categoryRepository->getCategoryById($id);

        if (!$category) {
            return $this->respondNotFound('The category was not found');
        }

        $response = $this->processItem($category, new CategoryTransformer);

        return $response;

    }

    public function edit($id, CategoryRequest $request)
    {

        $data = [
            "name" => $request->get('name')
        ];

        $result = $this->categoryRepository->updateCategory($id, $data);

        if ($result) {
            return $this->respondSuccess('Category updated successfully');
        }

        return $this->respondInternalError('An error ocurred while update category');

    }

}
