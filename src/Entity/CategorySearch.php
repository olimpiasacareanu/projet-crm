<?php

namespace App\Entity;
use Doctrine\ORM\Mapping\Entity as ORM;
use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping\JoinColumn;

class CategorySearch
{

    private $category;


    public function getCategory()
    {
        return $this->category;
    }


    public function setCategory($category): self
    {
        $this->category = $category;

        return $this;
    }


}