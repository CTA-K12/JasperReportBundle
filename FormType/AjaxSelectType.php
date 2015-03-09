<?php

namespace Mesd\Jasper\ReportBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AjaxSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'reportajaxselect';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $event->stopPropagation();
        });
    }
}