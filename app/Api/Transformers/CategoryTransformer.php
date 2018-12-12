<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use App\Api\Models\Category;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {
        return [
            'id' => (int) $category->id,
            'name' => $category->name
        ];
    }
}
