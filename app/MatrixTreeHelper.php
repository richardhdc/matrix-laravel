<?php

namespace App;

class MatrixTreeHelper 
{
    private $parent = '';
    private $position = '';
    private $childrow = '';
    
    public function register($num) 
    {
        $this->assign();
        $this->getRow();
        $tree = Tree::create(['maxtrix_number' => $num, 'matrix_row' => $this->childrow]);
        $this->assignToParent($tree->id);
        return ['child' => $tree->id, 'parent' => $this->parent, 'position' => $this->getText($this->position)];
    }
    
    private function getText($pos)
    {
        switch ($pos) {
            case 'left_child':
                return 'LEFT';
            case 'mid_child':
                return 'MIDDLE';
            case 'right_child':
                return 'RIGHT';
        }
    }
    
    private function getRow()
    {
        $treerow = TreeRow::orderBy('id', 'desc')->first();
        $this->childrow = $treerow->matrix_row;
    }
    
    private function generateRow()
    {
        if (!$this->generateFirstRow()) {
            $treerow = TreeRow::orderBy('id', 'desc')->first();
            $tree = Tree::where('matrix_row', $treerow->matrix_row)->count();
            if ($treerow->total_number_of_child == $tree) {
                $matrix_row = $treerow->matrix_row + 1;
                $total_child = $treerow->total_number_of_child * 3;
                $this->createTreeRow($matrix_row, $total_child);
            }
        }
    }
    
    private function generateFirstRow()
    {
        $treerow = TreeRow::orderBy('id', 'desc')->first();
        if (!$treerow) {
            $this->createTreeRow(1, 1);
            return true;
        }
        return false;
    }
    
    private function createTreeRow($row, $total) 
    {
        $data = [
            'matrix_row' => $row,
            'total_number_of_child' => $total,
        ];
        TreeRow::create($data);
    }
    
    private function assign()
    {
        $this->generateRow();
        $check = Tree::get();
        if ($check->count()) {
            $row = $this->getIncompleteRow();
            if (!$this->checkLeftWithVacant($row)) {
                if (!$this->checkMidWithVacant($row)) {
                    $this->checkRightWithVacant($row);
                }
            }
        }
    }
    
    private function getIncompleteRow() 
    {
        $treerow = Tree::orderBy('id', 'asc')->get();
        $filtered = $treerow->filter(function ($item, $key) {
            return ($item->left_child == null || $item->mid_child == null || $item->right_child == null);
        });
        $data = $filtered->first();
        return $data->matrix_row;
    }
    
    
    private function assignToParent($id)
    {
        if ($this->parent && $this->position) {
            Tree::where('id', $this->parent)->update([$this->position => $id]);
        }
    }
    
    private function checkLeftWithVacant($row)
    {
        $tree = Tree::where('left_child', null)
            ->where('mid_child', null)
            ->where('right_child', null)
            ->where('matrix_row', $row)
            ->first();
        
        if ($tree) {
            $this->parent = $tree->id;
            $this->position = 'left_child';
            return true;
        }
        return false;
    }
    
    private function checkMidWithVacant($row)
    {
        $tree = Tree::where('left_child', '<>', null)
            ->where('mid_child', null)
            ->where('right_child', null)
            ->where('matrix_row', $row)
            ->first();
            
        if ($tree) {
            $this->parent = $tree->id;
            $this->position = 'mid_child';
            return true;
        }
        return false;
    }
    
    private function checkRightWithVacant($row)
    {
        $tree = Tree::where('left_child', '<>', null)
            ->where('mid_child', '<>', null)
            ->where('right_child', null)
            ->where('matrix_row', $row)
            ->first();
            
        if ($tree) {
            $this->parent = $tree->id;
            $this->position = 'right_child';
            return true;
        }
        return false;
    }
}