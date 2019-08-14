<?php

namespace SAM\CommonBundle\Admin;

use FOS\UserBundle\Model\UserManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UserAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'sonata_user';
    protected $baseRoutePattern = 'user';
    
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @required
     *
     * @param UserManagerInterface $userManager
     *
     * @return $this
     */
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;

        return $this;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('email', null, ['label' => 'Email'])
            ->add('notifiedByEmail', null, ['label' => 'Notif par e-mail'])
            ->add('job', null, ['label' => 'Poste'])
            ->add('code', TextType::class, ['label' => 'Trigramme'])
            ->add('roles', ChoiceType::class, [
                    'label' => 'Rôles',
                    'multiple' => true,
                    'choices' => $this->getRoles(),
                ]
            )
        ;

        if (!$this->getSubject()->getId()) {
            $form->add('plain_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
            ]);
        }

        if ($this->isCurrentRoute('edit')) {
            $user                       = $this->getSubject();
            $pictureManager             = $this->getConfigurationPool()->getContainer()->get('SAM\CommonBundle\Manager\PictureManager');
            $fileFieldOptions           = ['required' => false];
            $fileFieldOptions['help']   = '<img src="'.$pictureManager->getPicture($user, ['imagineFilter' => 'user_contact_thumb_sm']).'" class="admin-preview"/>';

            $form->add('pictureFile', FileType::class, $fileFieldOptions);
        }
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('email', null, ['label' => 'Email'])
            ->add('code', null, ['label' => 'Trigramme'])
        ;
    }
    
    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('email', null, ['label' => 'Email'])
            ->add('code', null, ['label' => 'Trigramme'])
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                ]
            ])
        ;
    }

    /**
     * @param \SAM\CommonBundle\Entity\User $object
     */
    public function prePersist($object)
    {
        $object->setUsername($object->getEmail());
        $this->userManager->updateUser($object);
    }

    /**
     * @param \SAM\CommonBundle\Entity\User $object
     */
    public function preUpdate($object)
    {
        $object->setUsername($object->getEmail());
        $this->userManager->updateUser($object);
    }

    private function getRoles()
    {
        $roles = [];

        foreach ($this->getConfigurationPool()->getContainer()->getParameter('security.role_hierarchy.roles') as $key => $value) {
            foreach ($value as $role) {
                $roles[$role] = $role;
            }    
        }

        return $roles;
    }
}
