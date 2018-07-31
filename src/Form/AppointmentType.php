<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Form;

use App\Entity\Appointment;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Controller\AppointmentService;

class AppointmentType extends AbstractType 
{

    private $em;
    private $service;
    private $tokenStorage;
    private $calendar;

    /**
     * @param EntityManagerInterface $em
     * @param AppointmentService $service
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em, 
        AppointmentService $service,
        TokenStorageInterface $tokenStorage,
        $calendar
    ) {
        $this->em = $em;
        $this->service = $service;
        $this->tokenStorage = $tokenStorage;
        $this->calendar = $calendar;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $hours = array();
        
        if(isset($this->calendar['duration']) && is_numeric($this->calendar['duration'])) {
            $duration = $this->calendar['duration'];
            $iMax = 120 / $duration;
            for($i = 1; $i <= $iMax; $i++){
                $mins = $i * $duration;
                $hourConversion = date('H:i', mktime(0,$mins));
                $hours[$hourConversion] = $mins;
            }
        } 
        
        $builder
            ->add('startDate', DateTimeType::class, array(
                'label' => 'form.start_date',
                'attr' => ['readonly' => true, 'class' => 'datetimepicker'],
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'yyyy/MM/dd HH:mm',
            ))

            ->add('duration', ChoiceType::class, array(
                'label' => 'form.duration',
                'placeholder' => false,
                'choices' => $hours,
            ))

            ->add('qtd', TextType::class, array(
                'label' => 'form.qtd',
            ))
            
            ->add('obs', TextareaType::class, array(
                'label' => 'form.obs',
                'required' => false,
            ))

            /* hidden fields */
            ->add('depart', HiddenType::class, array(
                'data' => 'depart',
            ))
            ->add('doc_id', HiddenType::class, array(
                'data' => 'doc_id',
            ))
            ->add('doc_num', HiddenType::class, array(
                'data' => 'doc_num',
            ))
            ->add('supplier_name', HiddenType::class, array(
                'data' => 'supplier_name',
            ))
            ->add('endDate', HiddenType::class, array(
                'data' => 'end_date',
            ))
        ;

        

        /*  FORNECEDORES */

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new \LogicException(
                'No user found.'
            );
        }

        $roles = $user->getRoles();
        foreach($roles as $role){
            if($role->getRole() == 'ROLE_SUPPLIER') {
                if(!empty($user->getSupplierId())) {
                    $supplierId = $user->getSupplierId();
                }
            }
        }

        if(!empty($supplierId)){
            $response = $this->service->getSupplierById($supplierId);
        } else {
            $response = $this->service->getSuppliers();
        }

        $suppliers = json_decode($response->getContent(), true);
        $builder->add('supplierId', ChoiceType::class, array(
            'label' => 'form.supplier',
            'placeholder' => "placeholder.suppliers",
            'choices' => $suppliers,
        ));


        /* Procurar OP/Encom */

        $builder
            ->add('search_model_op', TextType::class, array(
                'label' => 'form.search_model_op',
                'mapped' => false,
                'required' => false,
                'label_format' => '%name%',
            ))
        ;


        /* OPS/ENCOMS */

        $formModifier = function (FormInterface $form, $supplierId = null) {
            $ops = array();
            $models = array();
            /* Symfony\Component\HttpFoundation\Response */
            $data = null === $supplierId ? array() : $this->service->getOpBySupplierId($supplierId);
            if(!is_array($data)){
                $data = json_decode($data->getContent(), true);
                foreach($data as $op){
                    $ops[] = $this->service->opToString($op);
                    $models[] = $this->service->opToString($op, 'model');
                }

                /* workaround porque vêm arrays dentro do array */
                $arr = array();
                foreach($ops as $val) {
                    foreach($val as $k => $v) {
                        $arr[str_replace(' ',' ',$k)] = $v;
                    }
                }
                $ops = $arr;
                /* workaround porque vêm arrays dentro do array */
                $arr = array();
                foreach($models as $val) {
                    foreach($val as $k => $v) {
                        $arr[str_replace(' ',' ',$k)] = $v;
                    }
                }
                $models = $arr;
            }

            $form
                ->add('op', ChoiceType::class, array(
                    'label' => 'form.op',
                    'placeholder' => 'placeholder.op',
                    'mapped' => false,
                    'choices' => $ops,
                    'required' => false,
                ))
            ;

            $form
                ->add('model', ChoiceType::class, array(
                    'label' => 'form.model',
                    'placeholder' => 'placeholder.model',
                    'mapped' => false,
                    'choices' => $models,
                    'required' => false,
                ))
            ;

        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getSupplierId());
            }
        );

        $builder->get('supplierId')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $data);
            }
        );
        
    }
    
}
