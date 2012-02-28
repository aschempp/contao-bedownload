<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2008-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend field for frontend field "file upload"
 * Allows to download the uploaded file
 */
class FileDownload extends Widget implements uploadable
{
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Set a parameter
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'uploader':
				$this->uploader = $varValue;
	
				if ($varValue == true)
				{
					$this->blnSubmitInput = true;
				}
				break;
		}

		return parent::__set($strKey, $varValue);
	}


	/**
	 * Return a parameter
	 * @param string
	 * @return string
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'extensions':
				return ($this->extesnions != '') ? $this->extensions : $GLOBALS['TL_CONFIG']['uploadTypes'];
				break;

			case 'uploadFolder':
				return ($this->uploadFolder != '') ? $$this->uploadFolder : 'tl_files';
				break;
		}

		return parent::__get($strKey);
	}
	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$strFile = $this->value;
		$strReturn = '';

		if (is_array($strFile) && count($strFile))
		{
			$strZip = 'system/html/'.md5(serialize($strFile)).'.zip';
		
			if (!file_exists(TL_ROOT . '/' . $strZip) || is_dir(TL_ROOT . '/' . $strFile))
			{
                // Count number of files to know if we have any
				$count = 0;
				
				$objZip = new ZipWriter($strZip);
				
				foreach( $strFile as $file )
				{
				    if (file_exists(TL_ROOT . '/' . $file) || is_dir(TL_ROOT . '/' . $strFile))
				    {
				        $count++;
				        $objZip->addFile($file, basename($file));
				    }
				}
				
				// No files in zip archive. Remove it!
				if ($count == 0)
				{
				    $objZip->close();
				    unlink(TL_ROOT . '/' . $strZip);
				    $strReturn .= $GLOBALS['TL_LANG']['MSC']['nodownload'];
				}
				
				$objZip->close();
			}
			
			$strFile = $strZip;
		}
		
		if (!file_exists(TL_ROOT . '/' . $strFile) || is_dir(TL_ROOT . '/' . $strFile))
		{
            $strReturn .= $GLOBALS['TL_LANG']['MSC']['nodownload'];
		}
		else
		{
			$strReturn = sprintf('<a href="%s" onclick="window.open(this.href); return false;" id="%s" class="tl_upload%s">%s</a>', 
								  $strFile,
								  $this->strId,
								  (strlen($this->strClass) ? ' ' . $this->strClass : ''), 
								  $GLOBALS['TL_LANG']['MSC']['download']);
		}

		// Add upload form field
		if ($this->uploader)
		{
			$objUpload = $this->getUploadField();
			$strReturn .= '<br>' . $objUpload->generateWithError();
		}

		return $strReturn;
	}
	
	
	/**
	 * Recursively validate an input variable
	 * @param mixed
	 * @return mixed
	 */
	protected function validator($varValue)
	{
		$objUpload = $this->getUploadField();
		$objUpload->validate();

		// Inherit the errors from upload field
		if ($objUpload->hasErrors())
		{
			$this->class = 'error';
			$this->arrErrors = $objUpload->getErrors();
		}

		// Return current value if no file was uploaded
		if (!isset($_SESSION['FILES'][$this->strName]))
		{
			return $this->value;
		}

		$strFile = $_SESSION['FILES'][$this->strName]['tmp_name'];
		unset($_SESSION['FILES'][$this->strName]);

		return str_replace(TL_ROOT . '/', '', $strFile);
	}
	
	
	/**
	 * Return an upload field object as array
	 * @return object
	 */
	private function getUploadField()
	{
		$arrField = array
		(
			'name' => $this->strName,
			'inputType' => 'upload',
			'eval' => array('storeFile'=>true, 'uploadFolder'=>$this->uploadFolder, 'extensions'=>$this->extensions, 'doNotOverwrite'=>$this->doNotOverwrite)
		);

		return new FormFileUpload($this->prepareForWidget($arrField, $arrField['name']));
	}
}

