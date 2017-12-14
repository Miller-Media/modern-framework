<?php
/**
 * Plugin Class File
 *
 * Created:   April 2, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.3.12
 */
namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\Singleton;

/**
 * Symfony Class
 */
class Symfony extends Singleton
{
	/**
	 * @var	self			Required for singletons
	 */
	protected static $_instance;
	
	/**
	 * @var		FormFactory
	 */
	protected $formFactory;
	
	/**
	 * Set the form factory
	 *
	 * @param	FormFactory			$formFactory			The form factory
	 * @return	void
	 */
	public function setFormFactory( \Symfony\Component\Form\FormFactoryInterface $formFactory )
	{
		$this->formFactory = $formFactory;
	}
	
	/**
	 * Get form factory
	 *
	 * @return	FormFactory
	 */
	public function getFormFactory()
	{
		if ( ! isset( $this->formFactory ) )
		{
			$csrfGenerator = new \Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator();
			$csrfStorage = new \Modern\Wordpress\Symfony\WPTokenStorage();
			$csrfTokenManager = new \Symfony\Component\Security\Csrf\CsrfTokenManager( $csrfGenerator, $csrfStorage );
			$csrfExtension = new \Symfony\Component\Form\Extension\Csrf\CsrfExtension( $csrfTokenManager );
			
			$validator = \Symfony\Component\Validator\Validation::createValidator();
			$validatorExtension = new \Symfony\Component\Form\Extension\Validator\ValidatorExtension( $validator );
			
			$httpFoundationExtension = new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension();

			$formFactory = \Symfony\Component\Form\Forms::createFormFactoryBuilder()
				->addExtension( $csrfExtension )
				->addExtension( $validatorExtension )
				->addExtension( $httpFoundationExtension )
				->getFormFactory();
				
			$this->setFormFactory( $formFactory );
		}
		
		return $this->formFactory;
	}
	
	
	
}
