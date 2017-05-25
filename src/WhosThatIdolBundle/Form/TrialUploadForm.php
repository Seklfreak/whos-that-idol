<?php
/**
 * Created by PhpStorm.
 * User: sekl
 * Date: 25.05.2017
 * Time: 10:56
 */

namespace WhosThatIdolBundle\Form;

use WhosThatIdolBundle\Entity\TrialUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class TrialUploadForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idolPicture', FileType::class, array(
                'label' => 'Image (JPEG or PNG)',
                'constraints' => array(
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG or PNG',
                    ])
                )
            ))
            ->add('submit', SubmitType::class, array('label' => 'Upload'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => TrialUpload::class,
        ));
    }
}