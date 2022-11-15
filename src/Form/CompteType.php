<?php

namespace App\Form;

use App\Entity\CompteBenevole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mail', TextType::class)
            ->add('roles', ChoiceType::class, ['choices' => [ 'benevole' => "ROLE_BENEVOLE", 'banque alimentaire' => "ROLE_BA" ]])
            ->add('password', PasswordType::class)
            ->add('contact')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompteBenevole::class,
        ]);
    }
}
