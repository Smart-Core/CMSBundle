<?php
namespace SmartCore\Bundle\EngineBundle\Form\Tree;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use SmartCore\Bundle\EngineBundle\Entity\Folder;

class FolderLoader implements EntityLoaderInterface
{
    /**
     * @var EntityRepository
     */
    private $repo;

    protected $result;

    protected $level;

    public function __construct(ObjectManager $em, $manager = null, $class = null)
    {
        $this->repo = $em->getRepository($class);
    }

    /**
     * Returns an array of entities that are valid choices in the corresponding choice list.
     *
     * @return array The entities.
     */
    public function getEntities()
    {
        $this->result = array();
        $this->level = 0;

        $this->addChild();
        return $this->result;
    }

    protected function addChild($parent_folder = null)
    {
        $level = $this->level;
        $ident = '';
        while ($level--) {
            $ident .= '&nbsp;&nbsp;';
        }

        $this->level++;

        $folders = $this->repo->findBy(
            array('parent_folder' => $parent_folder),
            array('position' => 'ASC')
        );

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            $folder->setFormTitle($ident . $folder->getTitle());
            $this->result[] = $folder;
            $this->addChild($folder);
        }

        $this->level--;
    }

    /**
     * Returns an array of entities matching the given identifiers.
     *
     * @param string $identifier The identifier field of the object. This method
     *                           is not applicable for fields with multiple
     *                           identifiers.
     * @param array $values The values of the identifiers.
     *
     * @return array The entities.
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        return $this->repo->findBy(
            array($identifier => $values)
        );
    }
}
