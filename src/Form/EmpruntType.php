<?php

namespace App\Form;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class EmpruntType extends AbstractType
{
     private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateEmprunt', null, [
                'widget' => 'single_text',
                'disabled' => true, 
            ])
            ->add('dateRetourPrevue', null, [
                'widget' => 'single_text',
                 'disabled' => true, 
            ]);

            // Only admin can edit actual status or dates freely, but for creation we often just want defaults.
            // For simplicity, let's keep it simple.
            
         if ($this->security->isGranted('ROLE_ADMIN')) {
              $builder->add('dateRetourEffective', null, [
                'widget' => 'single_text',
              ]);
              $builder->add('statut', ChoiceType::class, [
                  'choices' => [
                      'En cours' => 'en cours',
                      'Retourné' => 'retourné',
                      'En retard' => 'en retard',
                  ]
              ]);
              $builder->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ]);
         } else {
              // User shouldn't see these fields as editable
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
            'data_class' => Emprunt::class,
        ]);
    }
}
