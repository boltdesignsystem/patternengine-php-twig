<?php

/*!
 * Twig Pattern Engine Loader Class - Filesystem
 *
 * Copyright (c) 2014 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Sets an instance of Twig to deal with rendering of templates that aren't patterns
 *
 */

namespace PatternLab\PatternEngine\Twig\Loaders;

use \PatternLab\Config;
use \PatternLab\Dispatcher;
use \PatternLab\PatternEngine\Loader;
use \PatternLab\PatternEngine\Twig\TwigUtil;

class FilesystemLoader extends Loader {
	
	/**
	* Load a new Twig instance that uses the File System Loader
	*/
	public function __construct($options = array()) {
		
		// set-up default vars
		$twigDebug = Config::getOption("twigDebug");
		
		// set-up the paths to be searched for templates
		$filesystemLoaderPaths   = array();
		$filesystemLoaderPaths[] = $options["templatePath"];
		$filesystemLoaderPaths[] = $options["partialsPath"];
		
		// print_r($options["partialsPath"]);
		
		// $filesystemLoaderPaths[] = Config::getOption("patternSourceDir");
		
		// add source/_patterns subdirectories for Drupal theme template compatibility
		$patternSourceDir = Config::getOption("sourceDir").DIRECTORY_SEPARATOR."_patterns";
		$patternObjects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($patternSourceDir), \RecursiveIteratorIterator::SELF_FIRST);
		$patternObjects->setFlags(\FilesystemIterator::SKIP_DOTS);

		// sort the returned objects
		$patternObjects = iterator_to_array($patternObjects);
		ksort($patternObjects);
		

		foreach ($patternObjects as $name => $object) {
			if ($object->isDir()) {
				// print_r($object->getPathname());
				// print('
				// 
				// ');
				$filesystemLoaderPaths[] = $object->getPathname();
			}
		}
		
		
		
		
		
		// $filesystemLoaderPaths[] = $options["patternPaths"];
		// $filesystemLoaderPaths[] 
		// echo $options["templatePath"];
		// echo $options["patternPaths"];
		// echo $options["partialsPath"];
		
		// echo Config::getOption("patternSourceDir");
		
		
		// see if source/_macros exists. if so add it to be searchable
		$macrosPath = Config::getOption("sourceDir").DIRECTORY_SEPARATOR."_macros";
		if (is_dir($macrosPath)) {
			$filesystemLoaderPaths[] = $macrosPath;
		}
		
		// see if source/_layouts exists. if so add it to be searchable
		$layoutsPath = Config::getOption("sourceDir").DIRECTORY_SEPARATOR."_layouts";
		if (is_dir($layoutsPath)) {
			$filesystemLoaderPaths[] = $layoutsPath;
		}
		
		
		$patternsPath = Config::getOption("sourceDir").DIRECTORY_SEPARATOR."_patterns";
		if (is_dir($layoutsPath)) {
			$filesystemLoaderPaths[] = $patternsPath;
		}
		
		// $filesystemLoader = new \Twig_Loader_Filesystem($filesystemLoaderPaths);
		// $loaders[] = TwigUtil::addPaths($filesystemLoader, $patternSourceDir);
		// $filesystemLoaderPaths = TwigUtil::addPaths($filesystemLoader, $patternSourceDir);
		
		
		// set-up Twig
		$twigLoader = new \Twig_Loader_Filesystem($filesystemLoaderPaths);
		$instance   = new \Twig_Environment($twigLoader, array("debug" => $twigDebug));
		
		// customize Twig
		TwigUtil::setInstance($instance);
		TwigUtil::loadFilters();
		TwigUtil::loadFunctions();
		TwigUtil::loadTags();
		TwigUtil::loadTests();
		TwigUtil::loadDateFormats();
		TwigUtil::loadDebug();
		TwigUtil::loadMacros();
		
		// set-up the dispatcher
		$dispatcherInstance = Dispatcher::getInstance();
		$dispatcherInstance->dispatch("twigLoader.customize");
		$dispatcherInstance->dispatch("twigFilesystemLoader.customize");

		// get the instance
		$this->instance = TwigUtil::getInstance();
		
	}
	
	/**
	* Render a template
	* @param  {Array}        the options to be rendered by Twig
	*
	* @return {String}       the rendered result
	*/
	public function render($options = array()) {
		
		return $this->instance->render($options["template"].".".Config::getOption("patternExtension"), $options["data"]);
		
	}
	
}
