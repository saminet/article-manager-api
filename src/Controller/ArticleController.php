<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Employment;
use App\Service\ApiData;
use App\Service\RSSData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Person;
use App\Service\PersonService;
use App\Repository\EmploymentRepository;
use OA\Items;
use OA\Schema;
use OA\JsonContent;
use OpenApi\Attributes as OA;

#[Route('/api/articles', name: 'api_')]
class ArticleController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $manager)
    {

        $this->em = $manager;
    }

    #[Route('/', name: 'article_index', methods:['get'])]
    public function index(ManagerRegistry $doctrine, RSSData $rssData, ApiData $apiData): JsonResponse
    {
        $rssUrl = 'https://www.lemonde.fr/rss/une.xml';
        $rssData->saveRssData($rssUrl);

        $apiUrl = 'https://api.spaceflightnewsapi.net/v3/articles';
        $apiData->saveApiData($apiUrl);

        $data = $this->em->getRepository(Article::class)->findAll();

        return $this->json($data);
    }

    #[Route('/{id}', name: 'article_update', methods:['PUT', 'PATCH'])]
    #[OA\RequestBody(
        description: "Modifier un article",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type:'string'),
                new OA\Property(property: 'description', type:'string'),
            ]
        )
    )]
    public function updateArticle(Request $request, int $id): JsonResponse
    {
        $article = $this->em->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->json("Aucun article touvé pour l'id : " . $id, 404);
        }

        date_default_timezone_set('Europe/Paris');
        $today = new \DateTimeImmutable(date('Y-m-d H:i:s'));
        $data = json_decode($request->getContent(), true);
        $article->setTitle(!empty($data['title']) ? $data['title'] : "");
        $article->setDescription(!empty($data['description']) ? $data['description'] : "");
        $article->setUpdatedAt($today);

        $this->em->persist($article);
        $this->em->flush();

        $data =  [
            'id'       => $article->getId(),
            'title' => $article->getTitle(),
            'description' => $article->getDescription(),
            'updatedAt' => $article->getUpdatedAt()
        ];

        return $this->json($data);
    }

    #[Route('/{id}', name: 'delete_update', methods:['DELETE'])]
    public function deleteArticle(Request $request, int $id): JsonResponse
    {
        $article = $this->em->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->json('Aucun article trouvé.', 204);
        }

        $this->em->remove($article);
        $this->em->flush();

        return $this->json("Article supprimé avec l'id : " . $id, 200);
    }

    #[Route('/search-article', name: 'find_article_by_criteria', methods:['POST'])]
    #[OA\RequestBody(
        description: "Chercher les articles selon les critères ci-dessous",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type:'string'),
                new OA\Property(property: 'description', type:'string'),
            ]
        )
    )]
    public function findByCrtiteria(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $articles = $this->em
            ->getRepository(Article::class)
            ->findArticleByCriteria($data);

        if (!$articles || $articles == null) {
            return $this->json('Aucun article trouvé.', 400);
        }

        $data = [];
        foreach ($articles as $article) {
            $data[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
            ];
        }

        return $this->json($data);
    }
}
