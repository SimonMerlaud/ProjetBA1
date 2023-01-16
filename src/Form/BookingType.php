<?php

namespace App\Form;

use App\Entity\Booking;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('beginAt', DateTimeType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd  HH:mm',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
    ))
            ->add('endAt', DateTimeType::class,array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd  HH:mm',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker']
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
