<?php

namespace MESD\Jasper\ReportBundle\Services;

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
    private $path;

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
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Loads the data and prepares this class
     */
    public function init() {
        //If available load the config from the security file, else create a new array
        if ($this->path) {
            if ($this->loadSecurityConfiguration()) {
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
        $path = $pathOverride ?: $this->path;

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
        //Set the path to attempt to write to
        $path = $pathOverride ?: $this->path;

        //convert stuff and write out the file
        $dumper = new Dumper();
        $output = $dumper->dump($this->config);

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

        //Call the check node method
        return $this->checkNode($resourceUri, $this->securityContext->getToken()->getRoles());
    }


    /**
     * Sets the roles that can view a resource
     *
     * @param string   $resourceUri The uri of the resource on the server to view
     * @param array    $roles       The array of roles (string names) that can view this resource
     *
     * @return boolean              Whether the node was set or not (false if the node does not exist)
     */
    public function setRoles($resourceUri, $roles) {
        //Check if the data has been loaded yet, and load it if not
        if (!$this->ready) {
            $this->init();
        }

        //Call the create node method
        return $this->createNode($resourceUri, $roles);
    }


    //////////////////////
    // INTERNAL METHODS //
    //////////////////////


    /**
     * Creates a node in the configuration
     *
     * @param  string $resourceUri The uri of the resource on the report server to create a security configuration setting for
     * @param  array  $roles       The array of roles that will be allowed to view this resource
     */
    protected function createNode($resourceUri, $roles) {
        //Break up the uri on the slashes
        $nodes = preg_split('/[\\\/]/', $resourceUri);

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
        $nodes = preg_split('/[\\\/]/', $resourceUri);

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
     * Gets the Array that holds the report security settings.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the Array that holds the report security settings.
     *
     * @param array $config the security
     *
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets the File path to the report security yaml file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the File path to the report security yaml file.
     *
     * @param string $path the path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}