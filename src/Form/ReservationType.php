<?php

namespace App\Form;

use App\Entity\Livre;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class ReservationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateReservation', null, [
                'widget' => 'single_text',
                'disabled' => true,
            ]);
            
        if ($this->security->isGranted('ROLE_ADMIN')) {
             $builder->add('statut', ChoiceType::class, [
                  'choices' => [
                      'Active' => 'active',
                      'Annulée' => 'annulée',
                      'Expirée' => 'expirée',
                  ]
              ]);
             $builder->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ]);
        } else {
             $builder->add('livre', EntityType::class, [
                'class' => Livre::class,
                'choice_label' => 'titre',
                'disabled' => true,
             ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
