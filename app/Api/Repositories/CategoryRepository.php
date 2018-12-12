<?php

namespace Api\Repositories;

use Api\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Api\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{

    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function createCategory($data)
    {
        $result = $this->category->create($data);
        return $result;
    }

    public function getCategoryById($id)
    {
        $result = $this->category->where('id', $id)->first();
        return $result;
    }

    public function getCategories()
    {
        $result = $this->category->where('deleted', '!=', 1)->get();
        return $result;
    }

}
