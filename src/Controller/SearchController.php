<?php

namespace EMS\CoreBundle\Controller;

use EMS\CoreBundle\DependencyInjection\EMSCoreExtension;
use EMS\CoreBundle\Entity\AggregateOption;
use EMS\CoreBundle\Entity\SearchFieldOption;
use EMS\CoreBundle\Entity\SortOption;
use EMS\CoreBundle\Form\Form\AggregateOptionType;
use EMS\CoreBundle\Form\Form\ReorderBisType;
use EMS\CoreBundle\Form\Form\ReorderTerType;
use EMS\CoreBundle\Form\Form\ReorderType;
use EMS\CoreBundle\Form\Form\SearchFieldOptionType;
use EMS\CoreBundle\Form\Form\SortOptionType;
use EMS\CoreBundle\Service\AggregateOptionService;
use EMS\CoreBundle\Service\SearchFieldOptionService;
use EMS\CoreBundle\Service\SortOptionService;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Wysiwyg controller.
 *
 * @Route("/search-options")
 */
class SearchController extends AppController
{
    /**
     * Lists all Search options.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/", name="ems_search_options_index", methods={"GET","POST"})
     */
    public function indexAction(Request $request, SortOptionService $sortOptionService, AggregateOptionService $aggregateOptionService, SearchFieldOptionService $searchFieldOptionService)
    {
        $reorderSortOptionForm = $this->createForm(ReorderType::class);
        $reorderSortOptionForm->handleRequest($request);
        if ($reorderSortOptionForm->isSubmitted()) {
            $sortOptionService->reorder($reorderSortOptionForm);

            return $this->redirectToRoute('ems_search_options_index');
        }

        $reorderAggregateOptionForm = $this->createForm(ReorderBisType::class);
        $reorderAggregateOptionForm->handleRequest($request);
        if ($reorderAggregateOptionForm->isSubmitted()) {
            $aggregateOptionService->reorder($reorderAggregateOptionForm);

            return $this->redirectToRoute('ems_search_options_index');
        }

        $searchFieldOptionForm = $this->createForm(ReorderTerType::class);
        $searchFieldOptionForm->handleRequest($request);
        if ($searchFieldOptionForm->isSubmitted()) {
            $searchFieldOptionService->reorder($searchFieldOptionForm);

            return $this->redirectToRoute('ems_search_options_index');
        }

        return $this->render('@EMSCore/search-options/index.html.twig', [
                'sortOptions' => $sortOptionService->getAll(),
                'aggregateOptions' => $aggregateOptionService->getAll(),
                'searchFieldOptions' => $searchFieldOptionService->getAll(),
                'sortOptionReorderForm' => $reorderSortOptionForm->createView(),
                'aggregateOptionReorderForm' => $reorderAggregateOptionForm->createView(),
                'searchFieldOptionReorderForm' => $searchFieldOptionForm->createView(),
        ]);
    }

    /**
     * Creates a new Sort Option entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/sort/new", name="ems_search_sort_option_new", methods={"GET","POST"})
     */
    public function newSortOptionAction(Request $request, SortOptionService $sortOptionService, TranslatorInterface $translator)
    {
        $sortOption = new SortOption();
        $form = $this->createForm(SortOptionType::class, $sortOption, [
            'createform' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortOptionService->create($sortOption);

            return $this->redirectToRoute('ems_search_options_index');
        }

        return $this->render('@EMSCore/entity/new.html.twig', [
            'entity_name' => $translator->trans('search.sort_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new Search Field Option entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/search-field/new", name="ems_search_field_option_new", methods={"GET","POST"})
     */
    public function newSearchFieldOptionAction(Request $request, TranslatorInterface $translator, SearchFieldOptionService $searchFieldOptionService)
    {
        $searchFieldOption = new SearchFieldOption();
        $form = $this->createForm(SearchFieldOptionType::class, $searchFieldOption, [
            'createform' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchFieldOptionService->create($searchFieldOption);

            return $this->redirectToRoute('ems_search_options_index');
        }

        return $this->render('@EMSCore/entity/new.html.twig', [
            'entity_name' => $translator->trans('search.search_field_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new Agregate Option entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/aggregate/new", name="ems_search_aggregate_option_new", methods={"GET","POST"})
     */
    public function newAggregateOptionAction(Request $request, TranslatorInterface $translator, AggregateOptionService $aggregateOptionService)
    {
        $aggregateOption = new AggregateOption();
        $form = $this->createForm(AggregateOptionType::class, $aggregateOption, [
                'createform' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aggregateOptionService->create($aggregateOption);

            return $this->redirectToRoute('ems_search_options_index');
        }

        return $this->render('@EMSCore/entity/new.html.twig', [
                'entity_name' => $translator->trans('search.aggregate_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
                'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing SortOption entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/sort/{id}", name="ems_search_sort_option_edit", methods={"GET","POST"})
     */
    public function editSortOptionAction(Request $request, SortOption $sortOption, SortOptionService $sortOptionService, TranslatorInterface $translator)
    {
        $form = $this->createForm(SortOptionType::class, $sortOption);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $removeButton = $form->get('remove');
            if ($removeButton instanceof ClickableInterface && $removeButton->isClicked()) {
                $sortOptionService->remove($sortOption);

                return $this->redirectToRoute('ems_search_options_index');
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $sortOptionService->save($sortOption);

                return $this->redirectToRoute('ems_search_options_index');
            }
        }

        return $this->render('@EMSCore/entity/edit.html.twig', [
                'entity_name' => $translator->trans('search.sort_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
                'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing SearchFieldOption entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/search-field/{id}", name="ems_search_field_option_edit", methods={"GET","POST"})
     */
    public function editSearchFieldOptionAction(Request $request, SearchFieldOption $searchFieldOption, TranslatorInterface $translator, SearchFieldOptionService $searchFieldOptionService)
    {
        $form = $this->createForm(SearchFieldOptionType::class, $searchFieldOption);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $removeButton = $form->get('remove');
            if ($removeButton instanceof ClickableInterface && $removeButton->isClicked()) {
                $searchFieldOptionService->remove($searchFieldOption);

                return $this->redirectToRoute('ems_search_options_index');
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $searchFieldOptionService->save($searchFieldOption);

                return $this->redirectToRoute('ems_search_options_index');
            }
        }

        return $this->render('@EMSCore/entity/edit.html.twig', [
                'entity_name' => $translator->trans('search.search_field_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
                'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing AggregateOption entity.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/aggregate/{id}", name="ems_search_aggregate_option_edit", methods={"GET","POST"})
     */
    public function editAggregagteOptionAction(Request $request, AggregateOption $option, TranslatorInterface $translator, AggregateOptionService $aggregateOptionService)
    {
        $form = $this->createForm(AggregateOptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $removeButton = $form->get('remove');
            if ($removeButton instanceof ClickableInterface && $removeButton->isClicked()) {
                $aggregateOptionService->remove($option);

                return $this->redirectToRoute('ems_search_options_index');
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $aggregateOptionService->save($option);

                return $this->redirectToRoute('ems_search_options_index');
            }
        }

        return $this->render('@EMSCore/entity/edit.html.twig', [
                'entity_name' => $translator->trans('search.aggregate_option_label', [], EMSCoreExtension::TRANS_DOMAIN),
                'form' => $form->createView(),
        ]);
    }
}
