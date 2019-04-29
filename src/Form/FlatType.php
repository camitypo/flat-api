<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FlatType.
 */
class FlatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'occupancyDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                ]
            )
            ->add('street', TextType::class)
            ->add('zip', TextType::class)
            ->add('city', TextType::class)
            ->add('country', TextType::class)
            ->add('email', EmailType::class)
        ;
    }

    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Flat',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getName(): string
    {
        return 'flat';
    }
}
