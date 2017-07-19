<?php
/**
 * Form template file
 *
 * Created:   April 3, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Container id */
if ( ! empty( $id ) ) 
{
	echo "id=\"{$view->escape($id)}\" ";
}

/* Container attributes */
foreach ( $attr as $k => $v) 
{
	if ( in_array( $k, array( 'placeholder', 'title' ), true ) ) 
	{
		printf( '%s="%s" ', $view->escape( $k ), $view->escape( false !== $translation_domain ? $view[ 'translator' ]->trans( $v, array(), $translation_domain ) : $v ) );
	}
	elseif ( $v === true ) 
	{
		printf( '%s="%s" ', $view->escape( $k ), $view->escape( $k ) );
	}
	elseif ( $v !== false ) 
	{
		printf( '%s="%s" ', $view->escape( $k ), $view->escape( $v ) );
	}
}

