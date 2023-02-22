<?php

namespace App\Form;

use App\Entity\MainStart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('beginAt', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
            ))
            ->add('endAt', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MainStart::class,
        ]);
    }
}
