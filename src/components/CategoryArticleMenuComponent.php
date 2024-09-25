<?php

namespace App\components;

use App\Repository\CategoryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('CategoryMenuArticle')]
class CategoryArticleMenuComponent
{



    public function __construct(private CategoryRepository $categoryRepository)
    {

    }

    public function getCategories()
    {
        return $this->categoryRepository->findAll();
    }

}