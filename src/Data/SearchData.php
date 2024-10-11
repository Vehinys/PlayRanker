<?php

namespace App\Data;

use App\Entity\Category;

class SearchData
{

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var Genres[]
     */
    public $genres = [];

    /**
     * @var Platforms[]
     */
    public $platforms = [];

}