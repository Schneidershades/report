<?php

namespace MBLSolutions\Report\Repositories;

use Exception;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use MBLSolutions\Report\Models\Report;

class ManageReportRepository
{


    /**
     * Get a Report or make a New One
     *
     * @param null $id
     * @return Report
     */
    public function findOrNew($id = null): Report
    {
        if ($id !== 'null' && $id !== null) {
            $report = Report::findOrFail($id);
        }

        return $report ?? new Report;
    }

    /**
     * Create a new Report
     *
     * @param Request $request
     * @return Report
     * @throws Exception
     */
    public function create(Request $request): Report
    {
        try {
            DB::beginTransaction();

            /** @var Report $report */
            $report = Report::create($request->toArray());

            $this->handleReportRelations($report, $request);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report->fresh();
    }


    /**
     * Update a Report
     *
     * @param Report $report
     * @param Request $request
     * @return Report
     * @throws Exception
     */
    public function update(Report $report, Request $request): Report
    {
        try {
            DB::beginTransaction();

            logger(json_encode($request->toArray()));

            /** @var Report $report */
            $report->update($request->toArray());

            $this->handleReportRelations($report, $request);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report->fresh();
    }

    /**
     * Delete a Report
     *
     * @param Report $report
     * @return Report
     * @throws Exception
     */
    public function delete(Report $report): Report
    {
        $report->delete();

        return $report->fresh();
    }

    /**
     * Update or Create Report Relations
     *
     * @param Report $report
     * @param Request $request
     * @return ManageReportRepository
     */
    public function handleReportRelations(Report $report, Request $request): ManageReportRepository
    {
        if ($request->get('fields')) {
            $this->updateOrCreateFields($report, collect($request->get('fields')));
        }

        if ($request->get('selects')) {
            $this->updateOrCreateSelects($report, collect($request->get('selects')));
        }

        if ($request->get('joins')) {
            $this->updateOrCreateJoins($report, collect($request->get('joins')));
        }

        return $this;
    }

    /**
     * Update/Create Report Fields
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateFields(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->fields()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Update/Create Report Selects
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateSelects(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->selects()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Update/Create Report Joins
     *
     * @param Report $report
     * @param Collection $collection
     * @return ManageReportRepository
     */
    protected function updateOrCreateJoins(Report $report, Collection $collection): ManageReportRepository
    {
        $collection->each(static function ($data) use ($report) {
            $report->joins()->updateOrCreate(['id' => $data['id'] ?? null], $data);
        });

        return $this;
    }

    /**
     * Validate Report Request
     *
     * @param Request $request
     * @return ValidatorContract
     */
    public function validateReportRequest(Request $request): ValidatorContract
    {
        return Validator::make($request->all(),[
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'connection' => 'required|string|max:100',
            'display_limit' => 'required|numeric|between:25,500',
            'table' => 'required|string|max:128',
            'where' => 'nullable',
            'groupby' => 'nullable',
            'having' => 'nullable',
            'orderby' => 'nullable',
            'show_data' => 'required|boolean',
            'show_totals' => 'required|boolean',
            'active' => 'required|boolean',
            // Fields Validation
            'fields.*.label' => 'required|string',
            'fields.*.alias' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.model' => 'nullable|string',
            'fields.*.model_select_name' => 'nullable|string',
            'fields.*.model_select_value' => 'nullable|string',
            // Selects Validation
            'selects.*.column' => 'required|string',
            'selects.*.alias' => 'required|string',
            'selects.*.type' => 'required|string',
            'selects.*.column_order' => 'required|integer',
            // Joins Validation
            'joins.*.type' => 'required|string',
            'joins.*.table' => 'required|string',
            'joins.*.first' => 'required|string',
            'joins.*.operator' => 'required|string',
            'joins.*.second' => 'required|string',
        ], []);
    }

}