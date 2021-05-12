<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
	/**
	 * @Route("/test", name="test")
	 */
	public function test(): Response
	{

		$rrr = 'adasdasd';

		return $this->json([
			'status' => 'Welcome to your new controller!',
			'data' => ['src/Controller/FrontController.php']
		]);
	}

    /**
     * @Route("/{vueRouting}", priority=-1, name="front", defaults={"vueRouting": ""}, requirements={"vueRouting"=".+"})
     */
    public function index(): Response
    {
		return $this->render('front/index.html.twig', []);
    }



}
