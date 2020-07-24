<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
class Post
{
    private $id;
    private $userId;
    private $votes;
    private $title;
    private $content;
}