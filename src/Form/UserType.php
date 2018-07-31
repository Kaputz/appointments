<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Controller\UserService;

class UserType extends AbstractType 
{
    private $em;
    private $service;
    private $tokenStorage;

    /**
     * @param UserService $service
     */
    public function __construct(
        UserService $service
    ) {
        $this->service = $service;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $response = $this->service->getUsers();
        $content = json_decode($response->getContent(), true);
        $builder
            ->add('username', ChoiceType::class, array(
                'label' => 'form.user',
                'placeholder' => "placeholder.users",
                'choices' => $content,
            ))
            ->add('password', HiddenType::class, array(
                'data' => '321',
            ))

        ;
    }
}
