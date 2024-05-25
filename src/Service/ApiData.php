<?php

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class ApiData
{
    private $em;

    public function __construct(EntityManagerInterface $manager)
    {

        $this->em = $manager;
    }

    public function saveApiData($url)
    {
        $statisticsJson = file_get_contents($url);
        $statisticsObj = json_decode($statisticsJson);
        foreach ($statisticsObj as $item) {
            $title = $item->title;
            $description = $item->summary;
            $link = $item->url;
            $imageUrl = $item->imageUrl;
            $pubDate = $item->publishedAt;
            $pubDate = new \DateTimeImmutable($pubDate);
            $updatedAt = $item->updatedAt;
            $updatedAt = $updatedAt ? new \DateTimeImmutable($updatedAt) : null;

            $article = new Article();
            $articleExist = $this->em->getRepository(Article::class)->findOneBy(['title' => $title]);
            if ($title && !$articleExist) {
                $article->setTitle($title);
                $article->setDescription($description);
                $article->setLink($link);
                $article->setImageLink($imageUrl);
                $article->setPublishedAt($pubDate);
                $article->setUpdatedAt($updatedAt);
                $this->em->persist($article);
            }
        }

        $this->em->flush();
    }
}
