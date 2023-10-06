<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Section;

/**
 * Class Client
 *
 * @property \App\Models\Client $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class Client extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = false;

    /**
     * @var string
     */
    protected $title = 'Клиенты';

    /**
     * @var string
     */
    protected $alias;

    /**
     * Initialize class.
     */
    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-address-card');
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
            AdminColumn::link('name', 'Название', 'created_at')
                ->setSearchCallback(function($column, $query, $search){
                    return $query
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('created_at', 'like', '%'.$search.'%')
                    ;
                })
                ->setOrderable(function($query, $direction) {
                    $query->orderBy('created_at', $direction);
                })
            ,
            AdminColumn::text('created_at', 'Дата добавления', 'updated_at')
                ->setWidth('160px')
                ->setOrderable(function($query, $direction) {
                    $query->orderBy('updated_at', $direction);
                })
                ->setSearchable(false)
            ,
        ];

        $display = AdminDisplay::datatables()
            ->setName('firstdatatables')
            ->setOrder([[0, 'asc']])
            ->setDisplaySearch(true)
            ->paginate(25)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover')
        ;

        $display->setColumnFilters([
            AdminColumnFilter::select()
                ->setModelForOptions(\App\Models\Client::class, 'name')
                ->setLoadOptionsQueryPreparer(function($element, $query) {
                    return $query;
                })
                ->setDisplay('name')
                ->setColumnName('name')
                ->setPlaceholder('Все клиенты')
            ,
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
        $fields1 = [
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('name', 'Название')
                    ->required(),
                AdminFormElement::text('counter_number', 'Номер счетчика')
                    ->required(),
                AdminFormElement::select('type', 'Тип клиента', config('constant.clients_type'))
                    ->required(),
                AdminFormElement::select('status', 'Сопровождаемые клиент', config('constant.clients_status'))
                    ->required()
                    ->setDefaultValue(1),
                AdminFormElement::html('<hr>')
            ], 'col-xs-12 col-sm-6 col-md-4 col-lg-4')
        ];

        $fields2 = [
            AdminFormElement::columns()->addColumn([
                AdminFormElement::html('<h3>Цели</h3>'),
                AdminFormElement::hasMany('goals', [
                    AdminFormElement::text('name', 'Название цели'),
                    AdminFormElement::text('goal_id', 'Идентификатор цели'),
                    AdminFormElement::select('type', 'Тип метрики',config('constant.goals_type')),

                ])
            ], 'col-xs-12 col-sm-6 col-md-6 col-lg-6')
        ];

        $fields3 = [
            AdminFormElement::columns()->addColumn([
                AdminFormElement::html('<h3>Профили</h3>'),
                AdminFormElement::hasMany('profiles', [
                    AdminFormElement::text('title', 'Название профиля'),
                    AdminFormElement::text('yandex_map_url', 'Ссылка на профиль'),
                    AdminFormElement::select('status', 'Активный', config('constant.clients_status'))
                    ->required()
                    ->setDefaultValue(1),

                ])
            ], 'col-xs-12 col-sm-6 col-md-6 col-lg-6')
        ];


        $tabs = AdminDisplay::tabbed();

        $tabs->setTabs(function ($id) use ($fields1, $fields2, $fields3) {
            $tabs = [];

            $tabs[] = AdminDisplay::tab(AdminForm::elements($fields1))->setLabel('Общая информация');
            $tabs[] = AdminDisplay::tab(AdminForm::elements($fields2))->setLabel('Цели');
            $tabs[] = AdminDisplay::tab(AdminForm::elements($fields3))->setLabel('Профили');

            return $tabs;
        });

        $form = AdminForm::card()->setElements([$tabs]);

        $form->getButtons()->setButtons([
            'save'  => new Save(),
            'save_and_close'  => new SaveAndClose(),
            'save_and_create'  => new SaveAndCreate(),
            'cancel'  => (new Cancel()),
        ]);

        return $form;
    }

    /**
     * @return FormInterface
     */
    public function onCreate($payload = [])
    {
        return $this->onEdit(null, $payload);
    }

    /**
     * @return bool
     */
    public function isDeletable(Model $model)
    {
        return true;
    }

    /**
     * @return void
     */
    public function onRestore($id)
    {
        // remove if unused
    }
}
