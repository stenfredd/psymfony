<?php

namespace App\User\Personal\Controller;


use App\Service\CheckValidation;
use App\Service\JsonDTO;

use App\User\Entity\User;
use App\User\Service\UserService;
use Swagger\Annotations as SWG;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Exception\ValidationException;
use Throwable;

/**
 * @Route("/api/user")
 */
class PersonalController extends AbstractController
{
	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct
	(
		UserService $userService
	)
	{
		$this->userService = $userService;
	}

	/**
	 * @Route("/personal-data", name="get_user_personal_data", methods={"GET"})
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')", statusCode=403, message="Access denied")
	 * @SWG\Tag(name="Personal")
	 * @SWG\Response(
	 *     response=200,
	 *     description="Returns user perosnal data",
	 *     @SWG\Schema(
	 *         @SWG\Property(property="id", type="integer"),
	 *         @SWG\Property(property="active", type="integer"),
	 *         @SWG\Property(property="email", type="string"),
	 *         @SWG\Property(property="nickname", type="string"),
	 *         @SWG\Property(property="permissions", type="array", @SWG\Items(type="string")),
	 *         @SWG\Property(property="created_at", type="string"),
	 *     )
	 * )
	 * @param Request $request
	 * @return Response
	 */
	public function personalData(Request $request): Response
	{
		/** @var $user User */
		$user = $this->getUser();

		$data = $this->userService->getUserData($user);

		return $this->json([
			'status' => 'success',
			'data' => $data
		]);
	}

}
