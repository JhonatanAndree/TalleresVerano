<?php
namespace Models\Interfaces;

interface CrudInterface {
    public function getById($id);
    public function getAll($filters = [], $page = 1, $limit = 10);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function restore($id);
}