<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Controller\UserService;
use App\Repository\RoleRepository;

class UserController extends AbstractController
{

    /**
     * Index da classe
     * 
     * @Route("/user", name="user")
     */
    public function indexAction(
        Request $request
    ) {
        /* get all entities */
        $repository = $this->getDoctrine()
            ->getRepository(User::class);
        $users = $repository->findAll();

        /* format array as JSON */
        $arr = Array();
        foreach ($users as $user) {
            $arr[] = [
                'username' => $user->getUsername(),
                'name' => $user->getName(),
                'isActive' => $user->getIsActive(),
                'roles' => $user->getRoles(),
            ];
        }

        /* render frontend */
        return $this->render(
            'user/index.html.twig',
            array(
                'users' => $users,
                'usersJson' => $arr
            )
        );
    }

    /**
     * @Route("/user/new", name="user_new")
     */
    public function newAction(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder,
        UserService $service,
        RoleRepository $roleRepo
    ) {
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user, array());

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            //get data from db
            $response = $service->getUserData($user->getUsername());
            $data = json_decode($response->getContent(), true);
            $data = reset($data);

            //set data do object
            $user->setVangId($data['utilizador']);
            $user->setName($data['nome']);
            $user->setPlainPassword($data['password']);
            $user->setRoles($roleRepo->findRole("ROLE_EMPLOYEE"));

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'flash.new.user');

            return $this->redirectToRoute('user');
        }

        // render registration form
        return $this->render(
            'user/new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/user/delete/{id}", requirements={"id" = "\d+"}, name="user_delete")
     */
    public function deleteAction(
        User $user,
        UserService $service
    ) {
        /* set status */
        $active = $user->getIsActive();
        if($active){
            $user->setIsActive(0);
        } else {
            $user->setIsActive(1);
        }

        

        $entityManager = $this->getDoctrine()->getManager();
        //$entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'flash.delete');

        return $this->redirectToRoute(
            'user'
        );
    }

}
