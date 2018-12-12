<?php

namespace Api\Repositories\Interfaces;

interface CategoryRepositoryInterface
{
    public function createCategory($data);

    public function getCategoryById($id);

    public function getCategories();
}
