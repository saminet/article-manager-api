<?php

namespace App\Service;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

class RSSData
{
    private $em;

    public function __construct(EntityManagerInterface $manager)
    {

        $this->em = $manager;
    }

    public function saveRssData($url)
    {
        $rss = simplexml_load_file($url) or die("Failed to load");

        foreach ($rss->channel->item as $item) {
            $title = $item->title;
            $description = $item->description;
            $link = $item->link;
            $copyright = $item->copyright;
            //media tag
            $media = $item->children('media', true)->content->attributes();
            $image = $media->url;
            $imageUrl = (array) $image;
            $imageUrl = $imageUrl[0];
            //end media tag
            $pubDate = $item->pubDate;
            $pubDate = new \DateTimeImmutable($pubDate);

            $article = new Article();
            $articleExist = $this->em->getRepository(Article::class)->findOneBy(['title' => $title]);
            if ($title && !$articleExist) {
                $article->setTitle($title);
                $article->setDescription($description);
                $article->setLink($link);
                $article->setImageLink($imageUrl);
                $article->setAuthor($copyright);
                $article->setPublishedAt($pubDate);
                $this->em->persist($article);
            }
        }

        $this->em->flush();
    }
}
