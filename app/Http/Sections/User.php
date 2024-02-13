<?php

namespace App\Http\Sections;

use AdminForm;
use AdminColumn;
use AdminDisplay;
use App\Models\Role;
use AdminFormElement;
use AdminColumnFilter;
use SleepingOwl\Admin\Section;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;

/**
 * Class User
 *
 * @property User $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class User extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    /**
     * @var string
     */
    protected $title = 'Пользователи';

    /**
     * @var string
     */
    protected $alias;

    /**
     * Initialize class.
     */
    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-user-circle');
    }


    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::text('id', '№')->setWidth('50px')->setHtmlAttribute('class', 'text-center'),
            AdminColumn::link('name', 'Имя', 'created_at'),
            AdminColumn::text('created_at', 'Дата создания')
                ->setWidth('160px'),
        ];

        $display = AdminDisplay::datatables()
            ->setName('firstdatatables')
            ->setOrder([[1, 'asc']])
            ->paginate(25)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover')
        ;

        $display->setColumnFilters([
            null,
            AdminColumnFilter::select( \App\Models\User::class, 'name')
                ->setDisplay('name')
                ->setPlaceholder('Все пользователи')
                ->setColumnName('id'),
            null
        ]);
        $display->getColumnFilters()->setPlacement('card.heading');

        return $display;
    }

    /**
     * @param int|null $id
     * @param array $payload
     *
     * @return FormInterface
     */
    public function onEdit($id = null, $payload = [])
    {
        $form = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('name', 'Имя')
                    ->setValidationRules('string','max:255')
                    ->required(),
                AdminFormElement::text('email', 'email')
                    ->setValidationRules([Rule::unique('users')->ignore($id)]),
                AdminFormElement::select('status', 'Активный', config('constant.users_status'))
                    ->required()
                    ->setDefaultValue(1),
                AdminFormElement::password('password', 'Пароль'),
                AdminFormElement::multiselect('roles', 'Роль', Role::class)
                    ->setDisplay('name')
                
            ], 'col-xs-12 col-sm-6 col-md-4 col-lg-4'),
        ]);

        $form->getButtons()->setButtons([
            'save'  => new Save(),
            'save_and_close'  => new SaveAndClose(),
            'cancel'  => (new Cancel()),
        ]);

        return $form;
    }
    /**
     * @return FormInterface
     */
    public function onCreate($payload = [])
    {
        $form = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('name', 'Имя')
                    ->setValidationRules('string','max:255')
                    ->required(),
                AdminFormElement::text('email', 'email')
                    ->setValidationRules([Rule::unique('users')]),
                AdminFormElement::select('status', 'Активный', config('constant.users_status'))
                    ->required()
                    ->setDefaultValue(1),
                AdminFormElement::password('password', 'Пароль')
                    ->required(),
                AdminFormElement::multiselect('roles', 'Роль', Role::class)
                    ->setDisplay('name')
                    ->required()
                
            ], 'col-xs-12 col-sm-6 col-md-4 col-lg-4'),
        ]);

        $form->getButtons()->setButtons([
            'save'  => new Save(),
            'save_and_close'  => new SaveAndClose(),
            'cancel'  => (new Cancel()),
        ]);

        return $form;
    }

    /**
     * @return bool
     */
    public function isDeletable(Model $model)
    {
        return true;
    }

}
