<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\Appointment;
use App\Entity\AppointmentStatus;
use App\Form\AppointmentType;


class AppointmentController extends AbstractController
{

    /**
     * Index da classe
     * 
     * @Route("/appointment", name="appointment")
     * @Method("GET")
     */
    public function indexAction(
        Request $request
    ) {
        /* get all entities */
        $repository = $this->getDoctrine()
            ->getRepository(Appointment::class);
        $appointments = $repository->findAllActive();

        /* format array as JSON */
        $arr = Array();
        foreach ($appointments as $appointment) {
            $arr[] = [
                'id' => $appointment->getId(),
                'startDate' => $appointment->getStartDate()->format('Y-m-d H:i:s'),
                'endDate' => $appointment->getEndDate()->format('Y-m-d H:i:s'),
                'supplierId' => $appointment->getSupplierId(),
                'supplierName' => $appointment->getSupplierName(),
            ];
        }

        /* render frontend */
        return $this->render(
            'appointment/index.html.twig',
            array(
                'appointments' => $arr
            )
        );
    }

    /**
     * Cria "Doc"
     * @Route("/appointment/doc", name="appointment_doc")
     * @Method("GET")
     */
    public function docAction(
        Request $request
    ) {
         return $this->render(
            'appointment/doc.html.twig'
        );
    }

    /**
     * Persiste registo na base de dados.
     * 
     * @Route("/appointment", name="appointment_create")
     * @Method("POST")
     */
    public function createAction(
        Request $request, 
        AppointmentService $service,
        TranslatorInterface $translator
    ) {
        /* You can only access this using Ajax! */
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                    'message' => $translator->trans('message.ajaxAccessOnly')
            ), 400);
        }
    
        $entity = new Appointment();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /* data fim */

            $startDate = $entity->getStartDate();
            $duration = $entity->getDuration();

            /* $endDate = new \DateTime($startDate); */
            $tmpDate = \DateTimeImmutable::createFromMutable($startDate);
            $endDate = $tmpDate->add(new \DateInterval('PT' . $duration . 'M'));
            $entity->setEndDate($endDate);

            /* verifica datas */
            $repository = $this->getDoctrine()
                ->getRepository(Appointment::class);
            $appointments = 
                $repository->findByDateRange(
                    $entity->getStartDate(), 
                    $entity->getEndDate()
                );
            if(!empty($appointments)){
                return new JsonResponse(array(
                    'message' => $translator->trans('message.InvalidDateRange')
                ), 400);
            }

            /* nome fornecedor */
            $response = $service->getSupplierById($entity->getSupplierId());
            $supplier = json_decode($response->getContent());

            $entity->setSupplierName(key($supplier));

            /* dados da OP */
            $opId = $request->request->get('appointment')['op'];
            if(empty($opId)){ //handle da combo "modelo"
                $opId = $request->request->get('appointment')['model'];
            }

            $response = $service->getOpById($opId, $translator);
            $op = json_decode($response->getContent(), true);

            $op = reset($op);

            $entity->setDocId('OP');
            $entity->setDocNum($opId);
            $entity->setDepart($op['depart']);

            /* set marcação */
            $repository = $this->getDoctrine()
                ->getRepository(AppointmentStatus::class);
            $status = $repository->findOneByName('created');
            $entity->setStatus($status);

            /* set LastUpdatedUser */
            $user = $this->getUser();
            $entity->setLastUpdatedUser($user);

            /* set LastUpdatedDate */
            $entity->setLastUpdatedDate(new \DateTime());

            /* persistir entidade */
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            /* persiste em MSSQLSRV */
            $service->saveAppointment($entity);

            $this->addFlash('success', 'flash.new');

            return new JsonResponse(array('message' => 'Success!'), 200);
        }

        $response = new JsonResponse(
            array(
                'form' => $this->renderView(
                    'appointment/new.html.twig', 
                    array( 
                        'appointment' => $entity, 
                        'form' => $form->createView()
                    )
                )
            ), 200);
    
        return $response;
    }

    /**
     * Cria o form de "Create"
     * 
     * @param Appointment $entity The entity
     * @return SymfonyComponentFormForm The form
     */
    private function createCreateForm(
        Appointment $entity
    ) {
        $form = $this->createForm(AppointmentType::class, $entity,
            array(
                'action' => $this->generateUrl('appointment_create'),
                'method' => 'POST',
            )
        );
        return $form;
    }

    /**
     * Carrega HTML do form de novo registo.
     * 
     * @Route("/appointment/new", name="appointment_new")
     */
    public function newAction(
        Request $request, 
        TranslatorInterface $translator
    ) {
        /* You can only access this using Ajax!  */
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'message' => $translator->trans('message.ajaxAccessOnly')
            ), 400);
        }

        /* create insert form */
        $entity = new Appointment();

        $startDate = $request->request->get('startDate');
        $entity->setStartDate(\DateTime::createFromFormat('Y-m-d H:i:s', $startDate));

        $form = $this->createCreateForm($entity);

        $template = $this->render(
            'appointment/new.html.twig', 
            array( 
                'appointment' => $entity, 
                'form' => $form->createView()
            )
        )->getContent();

        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/appointment/delete/{id}", requirements={"id" = "\d+"}, name="appointment_delete")
     * @Method("GET")
     */
    public function deleteAction(
        Appointment $appointment,
        AppointmentService $service
    ) {
        /* set status */
        $repository = $this->getDoctrine()
                ->getRepository(AppointmentStatus::class);
        $status = $repository->findOneByName('deleted');
        $appointment->setStatus($status);

        /* set LastUpdatedUser */
        $user = $this->getUser();
        $appointment->setLastUpdatedUser($user);

        /* set LastUpdatedDate */
        $appointment->setLastUpdatedDate(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        //$entityManager->remove($appointment);
        $entityManager->flush();
        
        /* persiste em MSSQLSRV */
        $service->updateAppointment($appointment);

        $this->addFlash('success', 'flash.delete');

        return $this->redirectToRoute(
            'appointment'
        );
    }

    /**
     * Cria "List"
     * @Route("/appointment/list", name="appointment_list")
     * @Method("POST")
     */
    public function listAction(
        Request $request
    ) {
        /* You can only access this using Ajax! */
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'message' => $translator->trans('message.ajaxAccessOnly')
            ), 400);
        }

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $request->request->get('startDate'));
        $duration = $request->request->get('duration');
        $tmpDate = \DateTimeImmutable::createFromMutable($startDate);
        $endDate = $tmpDate->add(new \DateInterval('PT' . $duration . 'M'));

        $repository = $this->getDoctrine()
                ->getRepository(Appointment::class);
        $appointments = $repository->findByDateRange($startDate, $endDate);

        /* format array as JSON */
        $arr = Array();
        foreach ($appointments as $appointment) {
            $arr[] = [
                'id' => $appointment->getId(),
                'startDate' => $appointment->getStartDate()->format('Y-m-d'),
                'endDate' => $appointment->getEndDate()->format('Y-m-d'),
                'startHour' => $appointment->getStartDate()->format('H:i:s'),
                'endHour' => $appointment->getEndDate()->format('H:i:s'),
                'duration' => $appointment->getDuration(),
                //'obs' => $appointment->getObs(),
                //'docId' => $appointment->getDocId(),
                //'depart' => $appointment->getDepart(),
                //'docNum' => $appointment->getDocNum(),
                //'qtd' => $appointment->getQtd(),
                //'lastUpdatedDate' => $appointment->getLastUpdatedDate(),
                //'lastUpdatedUser' => $appointment->getLastUpdatedUser(),
                'supplierId' => $appointment->getSupplierId(),
                'supplierName' => $appointment->getSupplierName(),
            ];
        }

        $template = $this->render(
            'appointment/list.html.twig', 
            array( 
                'appointments' => $arr,
                'startDate' => $request->request->get('startDate')
            )
        )->getContent();

        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    

    /**
     * Cria "View"
     * @Route("/appointment/{id}", name="appointment_view")
     * @Method("POST")
     */
    public function viewAction(
        Appointment $entity, 
        Request $request, 
        AppointmentService $service,
        TranslatorInterface $translator
    ) {
        /* You can only access this using Ajax! */
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'message' => $translator->trans('message.ajaxAccessOnly')
            ), 400);
        }

        $response = $service->getOpById($entity->getDocNum());
        $op = json_decode($response->getContent(), true);
        $op = reset($op);

        $template = $this->render(
            'appointment/view.html.twig', 
            array( 
                'appointment' => $entity,
                'model' => $op['model'],
                'collection' => $op['collection']
            )
        )->getContent();

        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    
    
    
    

    /*     
        $new_str=$supplierId;
        file_put_contents("/var/www/html/log.txt", "\n" . $new_str, FILE_APPEND | LOCK_EX);
    }*/ 


    


    /* ------------------------------- NAO IMPLEMENTADO ------------------------------- */

    /**
     * @Route("/appointment/edit/{id}", name="appointment_edit")
     */
    public function updateAction(Request $request, Appointment $appointment)
    {
        $form = $this->createForm(AppointmentType::class, $appointment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $appointment = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', 'flash.edit');

            return $this->redirectToRoute(
                'appointment_show', [
                    'id' => $appointment->getId()
                ]
            );
        }
        
        return $this->render(
            'appointment/edit.html.twig', [
                'form' => $form->createView(),
                'appointment' => $appointment
            ]
        );
    }
    
} 

    