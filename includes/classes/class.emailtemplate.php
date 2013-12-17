<?php
/*
require 'lib/template';
template::setBaseDir('./templates');
 
$message = "Hey we hereby cordially invite you to attend our anual event being held on the weekend of the 30th June.<br /><br /> Please rsvp before 11PM tomorrow night";
Emailtemplate::setBaseDir('./assets/email_templates'); 
$html = Emailtemplate::loadTemplate('index', array('name'=>'Joseph', 'site'=>'http://thatguy.co.za', 'message'=>$message));

echo $html;
*/
 
class Emailtemplate
{
   private static $baseDir = '';
   private static $defaultTemplateExtension = '.html';
   
   public static function setBaseDir($dir){
     self::$baseDir = $dir;
   }
   
   public static function getBaseDir(){
     return self::$baseDir;
   }
   
   public static function setDefaultTemplateExtension($ext){
     self::$defaultTemplateExtension = $ext;
   }
   
   public static function getDefaultTemplateExtension(){
     return self::$defaultTemplateExtension;
   }

   public static function loadTemplate($template, $vars = array(), $subDir=''){
    if(empty($baseDir)){
      $baseDir = self::getBaseDir();
    }
     
    $templatePath = $baseDir.'/'.$template.''.self::getDefaultTemplateExtension();
    if(!file_exists($templatePath))
    {
      throw new Exception('Could not include template '.$templatePath);
    }

      $output = self::loadTemplateFile($templatePath, $vars);
      foreach($vars as $key=>$var)
      {
        $output = str_replace("{" . $key . "}", $var, $output);
      }
      return $output;
  }

  public static function renderTemplate($template, $vars = array(), $baseDir=null){
    echo self::loadTemplate($template, $vars, $baseDir);
  }

  private static function loadTemplateFile($__ct___templatePath__, $__ct___vars__){
    extract($__ct___vars__, EXTR_OVERWRITE);
    $__ct___template_return = '';
    ob_start();
    require $__ct___templatePath__;
    $__ct___template_return = ob_get_contents();
    ob_end_clean();
    return $__ct___template_return;
  }
}