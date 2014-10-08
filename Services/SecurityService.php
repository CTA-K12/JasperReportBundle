<?php

namespace Mesd\Jasper\ReportBundle\Services;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

use Symfony\Component\Security\Core\SecurityContext;

class SecurityService
{
    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * Array that holds the report security settings
     * @var array
     */
    private $config;

    /**
     * File path to the report security yaml file
     * @var string
     */
    private $securityFile;

    /**
     * Reference to the symfony security context
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * Whether the class has been initialized or not
     * @var boolean
     */
    private $ready;

    /**
     * Whether folders and reports that are above the default directory in the report server file system are restricted
     * @var boolean
     */
    private $maxLevelSetAtDefault;

    /**
     * The default folder (used here to check if the asked for folder is not above this when the max level flag is set)
     * @var string
     */
    private $defaultFolder;

    /**
     * The array of roles to use when a generating a new node and no other roles are given
     * @var array
     */
    private $defaultRoles;

    /**
     * If during the init process, a file was successfully loaded, then this will be true, false elsewise
     * @var boolean
     */
    private $fileFound;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param SecurityContext $securityContext Reference to symfony's security context
     */
    public function __construct(SecurityContext $securityContext) {
        //Set stuff
        $this->securityContext = $securityContext;

        //Set ready to false until the init function is called
        $this->ready = false;
        $this->fileFound = false;
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Loads the data and prepares this class
     */
    public function init() {
        //If available load the config from the security file, else create a new array
        if ($this->securityFile) {
            if (!$this->loadSecurityConfiguration()) {
                $this->config = array();
            }
        } else {
            $this->config = array();
        }
        //Set ready to true
        $this->ready = true;
    }


    /**
     * Loads the configuration from the
     * Note: if the file does not exist, a new array will be initialized in its place
     *
     * @param  string  $pathOverride Optional file path to attempt to read from instead of the default injected one
     * @param  boolean $debug        Whether to throw an error or to just return false on file operations
     *
     * @return boolean               Whether the read was successful or not
     */
    public function loadSecurityConfiguration($pathOverride = null, $debug = false) {
        //Set the path to attempt to read from
        $path = $pathOverride ?: $this->securityFile;

        //Load the file and set stuff
        if (file_exists($path)) {
            $yaml = new Parser();
            try {
                $this->config = $yaml->parse(file_get_contents($path));
            } catch(\Exception $e) {
                if ($debug) {
                    throw $e;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }

        //Set ready to true
        $this->ready = true;
        $this->fileFound = true;
        return true;
    }


    /**
     * Saves the security configuration settings to a file
     *
     * @param  string  $pathOverride Optional file path to attempt to read from instead of the default injected one
     * @param  boolean $debug        Whether to throw an error or to just return false on file operations
     *
     * @return boolean               Whether the write was successful or not
     */
    public function saveSecurityConfiguration($pathOverride = null, $debug = false) {
        if (!$this->ready) {
            //If the security file has not be loaded or started yet, return false
            return false;
        }
        //Set the path to attempt to write to
        $path = $pathOverride ?: $this->securityFile;

        //convert stuff and write out the file
        $dumper = new Dumper();
        $output = $dumper->dump($this->config, $this->determineMaxDepthOfConfig());

        try {
            file_put_contents($path, $output);
        } catch (\Exception $e) {
            if ($debug) {
                throw $e;
            } else {
                return false;
            }
        }

        return true;
    }


    /**
     * Determines whether the current user can view the resource with the given uri
     *
     * @param  string  $resourceUri The uri of the resource on the report server to check if viewable or not
     *
     * @return boolean              Whether the current user can view this resource or not
     */
    public function canView($resourceUri) {
        //Check if the data has been loaded yet, and load it if not
        if (!$this->ready) {
            $this->init();
        }

        //Check that this node is not beyond the default folder if the max level flag is set
        if ($this->maxLevelSetAtDefault) {
            //Check by seeing that the default folder is within the resource uri
            if (false === strpos($resourceUri, $this->defaultFolder)) {
                return false;
            }
        }

        // the exception message should pretty much explain the purpose of this block
        try {
            $roles = array_map(function ($role) {
                if ( 'Symfony\Component\Security\Core\Role\SwitchUserRole' == get_class($role) ) {
                    return $role->getRole();
                } else {
                    return $role."";
                }
            },$this->securityContext->getToken()->getRoles());
        } catch (\Exception $e) {
             throw new \Exception("Jasper Report Bundle security service requires roles to be returned as an array of strings or an array of objects with a __toString() method that returns the name of the role in the format specified by the security file. ", 0, $e);
        }

        //Call the check node method
        return $this->checkNode($resourceUri, $roles);
    }


    /**
     * Sets the roles that can view a resource
     *
     * @param string   $resourceUri The uri of the resource on the server to view
     * @param array    $roles       The array of roles (string names) that can view this resource
     *
     * @return boolean              Whether the node was set or not (false if the node does not exist)
     */
    public function setRoles($resourceUri, $roles = null) {
        //Check if the data has been loaded yet, and load it if not
        if (!$this->ready) {
            $this->init();
        }

        //If no roles were passed in, use the default
        $roles = $roles ?: $this->defaultRoles;

        //Call the create node method
        return $this->createNode($resourceUri, $roles);
    }


    /**
     * Checks whether a requested node currently exists in the security configuration
     *
     * @param  string  $resourceUri The resource uri to check
     *
     * @return boolean              Whether the resource uri currently has a node in the configuration
     */
    public function checkIfExists($resourceUri) {
        //Break up the uri on the slashes
        $nodes = preg_split('/(\\\|\\/)/', $resourceUri, -1, PREG_SPLIT_NO_EMPTY);

        //foreach node
        $ptr = &$this->config;
        foreach($nodes as $node) {
            if (!isset($ptr[$node])) {
                return false;
            }
        }

        return true;
    }


    /**
     * Determines the max depth of the config multidimensional array
     *
     * @return int The maximum depth
     */
    public function determineMaxDepthOfConfig() {
        return $this->arrayDepth($this->config, 1);
    }


    /**
     * Returns true if the configuration was successfully loaded from the report security file
     *
     * @return boolean Whether the config was loaded from the report security file
     */
    public function isLoadedFromFile() {
        return $this->fileFound;
    }


    //////////////////////
    // INTERNAL METHODS //
    //////////////////////


    /**
     * Recursively called function to determine the depth of an array
     *
     * @param  mixed $element Element to calculate depth on
     * @param  int   $depth   Depth level
     *
     * @return int            Depth at this point
     */
    protected function arrayDepth($element, $depth = 1) {
        if (is_array($element)) {
            $maxDepth = $depth;
            foreach($element as $el) {
                $d = $this->arrayDepth($el, $depth + 1);
                if ($d > $maxDepth) {
                    $maxDepth = $d;
                }
            }
            return $maxDepth;
        } else {
            return $depth;
        }
    }


    /**
     * Creates a node in the configuration
     *
     * @param  string $resourceUri The uri of the resource on the report server to create a security configuration setting for
     * @param  array  $roles       The array of roles that will be allowed to view this resource
     */
    protected function createNode($resourceUri, $roles) {
        //Break up the uri on the slashes
        $nodes = preg_split('/(\\\|\\/)/', $resourceUri, -1, PREG_SPLIT_NO_EMPTY);

        //foreach node, check if it currently exits, else create it
        $ptr = &$this->config;
        foreach($nodes as $node) {
            if (!isset($ptr[$node])) {
                $ptr[$node] = array('_roles' => $roles);
            }
            $ptr = &$ptr[$node];
        }
    }


    /**
     * Checks whether a resource can be viewed with the given roles
     *
     * @param  string  $resourceUri The uri of the resource on the report server to check security configurations for
     * @param  array   $roles       The array of roles that will be allowed to view this resource
     *
     * @return boolean              Whether this resource is viewable by the given roles
     */
    protected function checkNode($resourceUri, $roles) {
        //Break up the uri on the slashes
        $nodes = preg_split('/(\\\|\\/)/', $resourceUri, -1, PREG_SPLIT_NO_EMPTY);

        //Setup the valid flag
        $valid = false;

        //Go through the config until the resource is found, or its closest node and return what valid was at that point
        $ptr = &$this->config;
        foreach($nodes as $node) {
            if (isset($ptr[$node])) {
                //Increment the pointer
                $ptr = &$ptr[$node];
                //Check if valid at this node
                if (isset($ptr['_roles'])) {
                    //If the user has any of the roles required to see the resource, set valid to true, else set it to false
                    if (0 < count(array_intersect($ptr['_roles'], $roles))) {
                        $valid = true;
                    } else {
                        $valid = false;
                    }
                }
            } else {
                break;
            }
        }

        //return the valid flag
        return $valid;
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////

    /**
     * Gets the File path to the report security yaml file.
     *
     * @return string
     */
    public function getSecurityFile()
    {
        return $this->securityFile;
    }

    /**
     * Sets the File path to the report security yaml file.
     *
     * @param string $securityFile the security file
     *
     * @return self
     */
    public function setSecurityFile($securityFile)
    {
        $this->securityFile = $securityFile;

        return $this;
    }

    /**
     * Gets the Reference to the symfony security context.
     *
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * Sets the Reference to the symfony security context.
     *
     * @param SecurityContext $securityContext the security context
     *
     * @return self
     */
    public function setSecurityContext(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;

        return $this;
    }

    /**
     * Gets the Whether the class has been initialized or not.
     *
     * @return boolean
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     * Sets the Whether the class has been initialized or not.
     *
     * @param boolean $ready the ready
     *
     * @return self
     */
    public function setReady($ready)
    {
        $this->ready = $ready;

        return $this;
    }

    /**
     * Gets the Whether folders and reports that are above the default directory in the report server file system are restricted.
     *
     * @return boolean
     */
    public function getMaxLevelSetAtDefault()
    {
        return $this->maxLevelSetAtDefault;
    }

    /**
     * Sets the Whether folders and reports that are above the default directory in the report server file system are restricted.
     *
     * @param boolean $maxLevelSetAtDefault the max level set at default
     *
     * @return self
     */
    public function setMaxLevelSetAtDefault($maxLevelSetAtDefault)
    {
        $this->maxLevelSetAtDefault = $maxLevelSetAtDefault;

        return $this;
    }

    /**
     * Gets the The default folder (used here to check if the asked for folder is not above this when the max level flag is set).
     *
     * @return string
     */
    public function getDefaultFolder()
    {
        return $this->defaultFolder;
    }

    /**
     * Sets the The default folder (used here to check if the asked for folder is not above this when the max level flag is set).
     *
     * @param string $defaultFolder the default folder
     *
     * @return self
     */
    public function setDefaultFolder($defaultFolder)
    {
        $this->defaultFolder = $defaultFolder;

        return $this;
    }

    /**
     * Gets the The array of roles to use when a generating a new node and no other roles are given.
     *
     * @return array
     */
    public function getDefaultRoles()
    {
        return $this->defaultRoles;
    }

    /**
     * Sets the The array of roles to use when a generating a new node and no other roles are given.
     *
     * @param array $defaultRoles the default roles
     *
     * @return self
     */
    public function setDefaultRoles(array $defaultRoles)
    {
        $this->defaultRoles = $defaultRoles;

        return $this;
    }
}