<?php

namespace LMarco\MusicTubeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MusicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('title')
            // ->add('converted')
            ->add('youTubeUrl')
            // ->add('localPath')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LMarco\MusicTubeBundle\Entity\Music'
        ));
    }

    public function getName()
    {
        return 'lmarco_musictubebundle_musictype';
    }
}
