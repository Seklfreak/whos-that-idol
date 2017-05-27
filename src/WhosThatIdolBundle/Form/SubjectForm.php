<?php

namespace WhosThatIdolBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use WhosThatIdolBundle\Entity\Subject;
use WhosThatIdolBundle\Entity\TrialUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SubjectForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('groups', CollectionType::class, array(
                'entry_type'   => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('filename')
            ->add('source')
            ->add('picture')
            ->add('face')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Subject::class,
            'csrf_protection'   => false,
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'subject';
    }
}