<?php
/** *************************************************************************
* Object Oriented AutoLoader Class
* This class will parse fully qualified class names (FQCN) and also attempt to
* load (via require) single filenames from a specified set of default directories.
*
* A fully qualified class name has the form:
* \NamespaceName\SubNamespaceNames\ClassName
*
* System filenames must be the same as the class name being called.
* This class will also search a "DEFAULT" Namespace for directories when
* only the filename is passed to the AutoLoader. See below.
*
* This class uses an array of Namespaces and Directories, where the key
* is the Namespace name and the value is another array of Directories.
*
* Example:
* new MyClass;
* AutoLoader will search the internally specified DEFAULT Namespace for
* directories to require the file MyClass.php from.
* These directories must be registered to the AutoLoader manually with the
* DEFAULT Namespace. See below.
*
* Example:
* new \Namespace\MyClass
* AutoLoader will search the Namespace for directories to require the file
* MyClass.php from.
*
* Example:
* $autoloader->loadFile('header');
* AutoLoader will search the internally specified DEFAULT Namespace for
* directories to require the file header.php from.
*
* Add Namespaces and Directories to the AutoLoader with the addNamespace method:
* $prefix = Project Directory or Namespace (MyProject)
* $dir = An actual filesystem directory (C:/Documents/.../MyProject/classes)
* $autoloader->addNamespace($prefix, $dir);
*
* NOTE If you do not want to use Namespaces in your code, just register all
* your directories to the DEFAULT Namespace in the AutoLoader:
* $autoloader->addNamespace('DEFAULT', 'Your/Project/Filesystem/Directories');
*
* TO DO: Add a modifiable variable that will set the name of the default
* Namespace (in case someone wants to change that value, like perhaps if
* they actually use DEFAULT as their project Namespace in their code)
*****************************************************************************/

class AutoLoader
{

    /** *********************************************************************
    * Method to register loader with SPL autoloader stack.
    * @param string $function The autoload function to register, in this
    * case it is the internal method 'loadFile', so we must pass it as
    * an array containing the object and the method name within that object.
    * @return void
    *************************************************************************/
    public function registerAutoloader() {

        // A method of an instantiated object is passed as an array containing
        // an object at index 0 and the method name at index 1.
        $function = array($this, 'loadFile');
        spl_autoload_register($function);
    }


    /** *********************************************************************
    * The Namespace/Directory array. Key is Namespace, value is
    * an array of specified base Directories for that Namespace.
    * @var array
    *************************************************************************/
    protected $namespaces = array();

    public $defaultnamespace = 'DEFAULT';


    /** *********************************************************************
    * Method to add a Namespace and associated Directory to the array.
    * @param string $prefix The Namespace prefix.
    * @param string $dir A base Directory for class files in the Namespace.
    * @return void
    *************************************************************************/
    public function addNamespace($prefix, $dir) {

        // normalize the Namespace prefix string
        // (removes any backslashes from ends of string)
        // the Namespace is now in this format: \Namespace
        $prefix = '\\' . trim($prefix, '\\');

        // normalize the Directory string with a trailing separator
        $dir = trim($dir, DIRECTORY_SEPARATOR) . '/';

        // initialize the Namespace array
        if (isset($this->namespaces[$prefix]) === false) {
            $this->namespaces[$prefix] = array();
        }

        // add the Directory for the Namespace
        array_push($this->namespaces[$prefix], $dir);
    }


    /** *********************************************************************
    * Method to parse a fully qualified class name.
    * @param string $filename The fully-qualified class name.
    * @return mixed The mapped file name on success, or boolean false on
    * failure.
    *************************************************************************/
    public function loadFile($filename) {

        // normalize what should be the fully qualified class name
        // \<NamespaceName>(\<SubNamespaceNames>)*\<ClassName>
        $filename = '\\' . trim($filename, '\\');

        // work through the prefix variable, check directories in a loop
        $prefix = $filename;

        // start separating the fully qualified class name into possible
        // Namespace prefixes to check the namespaces array with:
        // get the last backslash in the fully qualified class name, check
        // for that prefix, try to require files, repeat
        while (false !== $pos = strrpos($prefix, '\\')) {

            // extract the prefix (without a trailing backslash) by trimming
            // the string at the last backslash (thus using everything before
            // the last backslash as the prefix)
            $prefix = substr($filename, 0, $pos);

            // if $filename was supplied without any backslashes
            // the $prefix at this step will be an empty string ""
            // Specify default Namespace as the Namespace prefix
            if($prefix == '') {
                $prefix = '\\' . $this->defaultnamespace;
            }

            // the rest of the string is the relative class name
            // without the leading backslash
            $relativeclass = substr($filename, $pos + 1);

            // use another internal class method to check if the resulting
            // file path is correct by attempting to require the class file
            $mappedfile = $this->checkFile($prefix, $relativeclass);

            // if the assembled file path is correct, return the file path
            // the file was loaded (required) by the checkFile method
            if ($mappedfile) {
                return $mappedfile;
            }

            // a mapped file was never found (darn, check those directory paths)
            return false;
        }
    }


    /** *********************************************************************
    * Method to retrieve a file with a Namespace prefix and relative class.
    * @param string $prefix The Namespace prefix.
    * @param string $relativeclass The relative class name.
    * return mixed Boolean false if no mapped file can be loaded, or the
    * name of the mapped file that was loaded.
    *************************************************************************/
    protected function checkFile($prefix, $relativeclass) {

        // is this Namespace prefix in the namespaces array?
        if (isset($this->namespaces[$prefix]) === false) {
            return false;
        }

        // a Namespace exists, look through the associated directories
        foreach ($this->namespaces[$prefix] as $dir) {

            // try to build a file path: use Directory in place of namesapce,
            // and prepare the relative class string as a file path
            $path = $dir . str_replace('\\', '/', $relativeclass) . '.php';

            // if the path is valid, get the file
            if (file_exists($path)) {
                require $path;
                return $path;
            }
        }

        // none of the directories worked, return false
        return false;
    } // close checkFile


} // close AutoLoader

 ?>
