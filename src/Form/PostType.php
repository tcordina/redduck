<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subcategory', EntityType::class, [
                'class' => SubCategory::class,
                'label' => 'Subredduck',
                'choice_label' => 'name',
            ])
            ->add('title')
            ->add('content', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Say something ...',
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Delete current image',
                'download_uri' => false,
                'image_uri' => true,
                'attr' => [
                    'class' => 'file-input',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
