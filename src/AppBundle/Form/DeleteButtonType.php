<?php
/**
 * Created by PhpStorm.
 * User: mwienk
 * Date: 6/6/17
 * Time: 4:22 PM
 */

namespace AppBundle\Form;


use Symfony\Component\Form\Extension\Core\Type\ButtonType;

class DeleteButtonType extends ButtonType
{

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'delete';
    }

}