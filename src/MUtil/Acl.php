<?php

/**
 *
 * @package    MUtil
 * @subpackage Acl
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Extends \Zend_Acl with a couple of overview functions
 *
 * @package    MUtil
 * @subpackage Acl
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Acl extends \Zend_Acl
{
    const PARENTS   = 'PARENTS';
    const INHERITED = 'INHERITED';

    /**
     * Adds an "allow" rule to the ACL
     *
     * @param  \Zend_Acl_Role_Interface|string|array     $roles
     * @param  string|array                             $privileges
     * @uses   \Zend_Acl::allow()
     * @return \Zend_Acl Provides a fluent interface
     */
    public function addPrivilege($roles, $privileges_args)
    {
        $privileges = \MUtil_Ra::args(func_get_args(), 1);

        return $this->allow($roles, null, $privileges);
    }

    public function echoRules()
    {
        \MUtil_Echo::r($this->_rules);
    }

    public function getPrivilegeRoles()
    {
        $results = array();

        if (isset($this->_rules['allResources']['byRoleId'])) {
            foreach ($this->_rules['allResources']['byRoleId'] as $role => $rule) {
                if (isset($rule['byPrivilegeId'])) {

                    foreach ($rule['byPrivilegeId'] as $privilege => $pdata) {
                        if (! isset($results[$privilege])) {
                            $results[$privilege] = array(
                                parent::TYPE_ALLOW => array(),
                                parent::TYPE_DENY  => array());
                        }

                        if (isset($pdata['type'])) {
                            if ($pdata['type'] === parent::TYPE_ALLOW) {
                                $results[$privilege][parent::TYPE_ALLOW][] = $role;
                            } elseif ($pdata['type'] === parent::TYPE_DENY) {
                                $results[$privilege][parent::TYPE_DENY][]  = $role;
                            }
                        }
                    }
                }
                // \MUtil_Echo::r($results);
            }
        }

        return $results;
    }

    /**
     * @param string|array $parents One or more role names
     * @return array roleId => roleId
     */
    public function getChildRoles($parents)
    {
        $registry = $this->_getRoleRegistry()->getRoles();
        
        $output = [];
        foreach ((array) $parents as $parent) {
            if (isset($registry[$parent]['children']) && $registry[$parent]['children']) {
                foreach ($registry[$parent]['children'] as $child => $data) {
                    $output[$child] = $child;
                }
            }
        }
        
        return $output;
    }

    /**
     * Retrieve an array of the current role and all parents
     *
     * @param string $role
     * @return array With identical keys and values roleId => roleId
     */
    public function getParentRoles($role)
    {
        $results = $this->getRoleAndParents($role);
        unset($results[$role]);
        return $results;
    }

    /**
     * Get the privileges for these parents
     *
     * @param array $parents
     * @return array privilege => setting
     */
    public function getPrivilegesForRoles(array $parents)
    {
        if (! $parents) {
            return array();
        }

        $rolePrivileges = $this->getRolePrivileges();
        $inherited      = array();
        foreach ($parents as $parent) {
            if (isset($rolePrivileges[$parent])) {
                $inherited = $inherited + array_flip($rolePrivileges[$parent][\Zend_Acl::TYPE_ALLOW]);
                $inherited = $inherited +
                    array_flip($rolePrivileges[$parent][\MUtil_Acl::INHERITED][\Zend_Acl::TYPE_ALLOW]);
            }
        }
        // Sneaks in:
        unset($inherited[""]);

        return array_combine(array_keys($inherited), array_keys($inherited));
    }

    /**
     * Retrieve an array of the current role and all parents
     *
     * @param string $role
     * @param array $parents
     * @return array With identical keys and values roleId => roleId
     */
    public function getRoleAndParents($role, $parents = array())
    {
        $results = $parents;
        $result = $this->_getRoleRegistry()->getParents($role);
        foreach($result as $roleId => $selRole) {
            if (!in_array($roleId, $results)) {
                $results = $this->getRoleAndParents($roleId, $results);
            }
            $results[$roleId] = $roleId;
        }
        $results[$role] = $role;
        return $results;
    }

    /**
     * Returns an array of roles with all direct and inherited privileges
     *
     * Sample output:
     * <code>
     *   [\MUtil_Acl::PARENTS]=>array(parent_name=>parent_object),
     *   [\Zend_Acl::TYPE_ALLOW]=>array([index]=>privilege),
     *   [\Zend_Acl::TYPE_DENY]=>array([index]=>privilege),
     *   [\MUtil_Acl::INHERITED]=>array([\Zend_Acl::TYPE_ALLOW]=>array([index]=>privilege),
     *                                 [\Zend_Acl::TYPE_DENY]=>array([index]=>privilege))
     * </code>
     *
     * @return array
     */
    public function getRolePrivileges()
    {
        $results = array();

        foreach ($this->getRoles() as $role) {
            $rules = $this->getPrivileges($role);
            $results[$role] = array(
                self::PARENTS => $this->_getRoleRegistry()->getParents($role),
                parent::TYPE_ALLOW => $rules[parent::TYPE_ALLOW],
                parent::TYPE_DENY => $rules[parent::TYPE_DENY]);

            //Haal overerfde rollen op
            if (is_array($results[$role][self::PARENTS])) {
                $role_inherited_allowed = array();
                $role_inherited_denied = array();
                foreach ($results[$role][self::PARENTS] as $parent_name => $parent) {
                    $parent_allowed = $results[$parent_name][parent::TYPE_ALLOW];
                    $parent_denied = $results[$parent_name][parent::TYPE_DENY];
                    $parent_inherited_allowed = $results[$parent_name][self::INHERITED][parent::TYPE_ALLOW];
                    $parent_inherited_denied = $results[$parent_name][self::INHERITED][parent::TYPE_DENY];
                    $role_inherited_allowed = array_merge($role_inherited_allowed, $parent_allowed, $parent_inherited_allowed);
                    $role_inherited_denied = array_merge($role_inherited_denied, $parent_denied, $parent_inherited_denied);
                }
                $results[$role][self::INHERITED][parent::TYPE_ALLOW] = array_unique($role_inherited_allowed);
                $results[$role][self::INHERITED][parent::TYPE_DENY] = array_unique($role_inherited_denied);
            }
        }

        return $results;
    }

    /**
     * @param string $privilege
     * @return array roleId => roleId
     */
    public function getRolesForPrivilege($privilege)
    {
        $output = [];
        foreach ($this->getRoles() as $role) {
            if ($this->isAllowed($role, null, $privilege)) {
                $output[$role] = $role;
            }
        }
        return $output;
    }
    
    /**
     * Returns all allow and deny rules for a given role
     *
     * Sample output:
     * <code>
     *   [\Zend_Acl::TYPE_ALLOW]=>array(<index>=>privilege),
     *   [\Zend_Acl::TYPE_DENY]=>array(<index>=>privilege)
     * </code>
     *
     * @param string $role
     * @return array
     */
    public function getPrivileges ($role) {
        $rule = $this->_getRules(null, $this->getRole($role));

        $results = array(parent::TYPE_ALLOW => array(),
                         parent::TYPE_DENY  => array());

        if (isset($rule['byPrivilegeId'])) {
            foreach ($rule['byPrivilegeId'] as $privilege => $pdata) {
                if (isset($pdata['type'])) {
                    if ($pdata['type'] === parent::TYPE_ALLOW) {
                        $results[parent::TYPE_ALLOW][] = $privilege;
                    } elseif ($pdata['type'] === parent::TYPE_DENY) {
                        $results[parent::TYPE_DENY][]  = $privilege;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Removes a previously set "allow" rule from the ACL
     *
     * @param  \Zend_Acl_Role_Interface|string|array     $roles
     * @param  string|array                             $privileges
     * @uses   \Zend_Acl::allow()
     * @return \Zend_Acl Provides a fluent interface
     */
    public function removePrivilege($roles, $privileges_args)
    {
        $privileges = \MUtil_Ra::args(func_get_args(), 1);

        return $this->removeAllow($roles, null, $privileges);
    }
}
