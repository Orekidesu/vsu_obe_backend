<?php

namespace App\Http\Resources\Api\V1\Faculty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseOutcomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'statement' => $this->statement,

            // ABCD components
            'abcd' => $this->whenLoaded('abcd', function () {
                return [
                    'audience' => $this->abcd->audience,
                    'behavior' => $this->abcd->behavior,
                    'condition' => $this->abcd->condition,
                    'degree' => $this->abcd->degree,
                ];
            }),

            // CPA information
            'cpa' => $this->whenLoaded('cpa', function () {
                return $this->cpa->cpa;
            }),

            // PO mappings
            'po_mappings' => $this->whenLoaded('pos', function () {
                return $this->pos->map(function ($po) {
                    return [
                        'po_id' => $po->id,
                        'po_name' => $po->name,
                        'po_statement' => $po->statement,
                        'ied' => $po->pivot->ied,
                    ];
                });
            }),

            // TLA tasks
            'tla_tasks' => $this->whenLoaded('tlaTasks', function () {
                return $this->tlaTasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'at_code' => $task->at_code,
                        'at_name' => $task->at_name,
                        'at_tool' => $task->at_tool,
                        'weight' => $task->weight,
                    ];
                });
            }),

            // TLA methods
            'tla_assessment_method' => $this->whenLoaded('tlaMethod', function () {
                return [
                    'id' => $this->tlaMethod->id,
                    'teaching_methods' => $this->tlaMethod->teaching_methods,
                    'learning_resources' => $this->tlaMethod->learning_resources,
                ];
            }),
        ];
    }
}
