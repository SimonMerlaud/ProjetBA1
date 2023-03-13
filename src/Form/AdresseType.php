<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codePostale',TextType::class,array('label'=>'Code postal '))
            ->add('ville',TextType::class,array('label'=>'Ville '))
            ->add('rue',TextType::class,array('label'=>'Rue '))
            ->add('numeroRue',IntegerType::class,array('label'=>'Numéro de la rue '))
            ->add('numeroAppart',IntegerType::class,['required'=>false,'label'=>'Numéro d\'appartement (facultatif)'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
