<?php
/**
 * Plugin Class File
 *
 * Created:   December 14, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace Modern\Wordpress\Helpers\Form\SymfonyForm;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * FormTypeExtension Class
 */
class FormTypeExtension extends AbstractTypeExtension
{
    /**
     * Extends the form type which all other types extend
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * Add the extra row_attr option
     *
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions( OptionsResolver $resolver )
    {
        $resolver->setDefaults(array(
            'row_attr' => array(),
            'prefix' => '',
            'suffix' => '',
			'row_prefix' => '',
			'row_suffix' => '',
        ));
    }

    /**
     * Pass the set row_attr options to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView( FormView $view, FormInterface $form, array $options )
    {
        $view->vars['row_attr'] = $options['row_attr'];
		$view->vars['prefix'] = $options['prefix'];
		$view->vars['suffix'] = $options['suffix'];
    }
}
