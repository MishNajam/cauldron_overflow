<?php


namespace App\Controller;

use App\Form\QuestionFormType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Question;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(QuestionRepository $repository)
    {
        $questions = $repository->findAllAskedOrderedByNewest();
        //long way -> using the Twig service directly
        //$html = $twigEnvironment->render('question/homepage.html.twig');
        //return new Response($html);

        //short way
        return $this->render('question/homepage.html.twig',[
            'questions' => $questions,
        ]);
    }

    /**
     * @Route("/questions/post")
     */
    public function post(EntityManagerInterface $entityManager)
    {
        $question = new Question();
        $question->setTitle('Teachers help')
        ->setSlug('Teachers-help'.rand(0,1000))
        ->setQuestion(<<<EOF
        Virtual Teachers of Reddit (Due to COVID-19), was it shocking to see how some of your kids actually live? 
        And if so, what was the most extreme story?
EOF
);
        if (rand(1, 10) > 2) {
            $question->setPublishedAt(new \DateTime(sprintf('-%d days', rand(1, 100))));
        }

//        dd($question);
        $entityManager->persist($question);
        $entityManager->flush();

        return new Response(sprintf(
                                'Success! Your post question id number is #%d, link: %s',
                                $question->getId(),
                                $question->getSlug()
                            ));

    }

    /**
    /**
     * @Route("/questions/{slug}", name="app_question_show")
     */
    public function show($slug,  EntityManagerInterface $entityManager)
    {

//        if($this->isDebug){
//            $this->logger->info('We are in debug mode!');
//        }

        $repository = $entityManager->getRepository(Question::class);
        /** @var  Question|null $question */
        $question = $repository->findOneBy(['slug' => $slug]);
        if (!$question) {
            throw $this->createNotFoundException(sprintf('no question found for link "%s"', $slug));
        }

        $answers = [
            'Make sure your cat is sitting purrrfectly still ',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        dump($this);

        return $this->render('/question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     *  @Route("/questions/{slug}/vote", name="app_question_vote", methods="POST")
     */
    public function questionVote(Question $question, Request $request, EntityManagerInterface $entityManager)
    {
        $direction = $request->request->get('direction');

        if ($direction === 'up') {
            $question->upVote();
        } elseif ($direction === 'down') {
            $question->downVote();
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_question_show', [
            'slug' => $question->getSlug()
        ]);
    }

}