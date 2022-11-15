<?php

namespace App\Form;

use App\Entity\Lieux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagasinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('nbPersonneNecessaire')
            ->add('datesMag')
            ->add('TypeLieux')
            ->add('adresse')
            ->add('contacts')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieux::class,
        ]);
    }
}
