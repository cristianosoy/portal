<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Util;

use Interop\Container\ContainerInterface;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Sprinkle\Core\Util\EnvironmentInfo;
use UserFrosting\Support\Exception\BadRequestException;

/**
 * URL Parameter util class.
 *
 * @author Kai Schröer (https://schroeer.co)
 */
class ParamParser
{
    /**
     * Get an object of our model by a given id url parameter.
     *
     * @param string $classMappingName The name of the model´s class mapping.
     * @param array $params An array of parameters including the id.
     * @return \UserFrosting\Sprinkle\Core\Database\Models\Model|null An instance of a model object.
     * @throws BadRequestException If the passed array with params does not contain the id.
     */
    public static function getObjectById($classMappingName, array $params)
    {
        /** @var \Interop\Container\ContainerInterface $ci */
        $ci = EnvironmentInfo::$ci;

        // Load the request schema
        $schema = new RequestSchema('schema://requests/get-by-id.json');

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and throw exception on validation errors.
        $validator = new ServerSideValidator($schema, $ci->translator);
        if (!$validator->validate($data)) {
            $ex = new BadRequestException();

            foreach ($validator->errors() as $idx => $field) {
                foreach ($field as $eidx => $error) {
                    $ex->addUserMessage($error);
                }
            }

            throw $ex;
        }

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $ci->classMapper;

        // Get the object by primary key
        $object = $classMapper->staticMethod($classMappingName, 'find', $data['id']);

        return $object;
    }
}
