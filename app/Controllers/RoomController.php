<?php

namespace App\Controllers;

use App\Models\RoomModel;

class RoomController extends BaseController
{
    protected RoomModel $roomModel;

    public function __construct()
    {
        $this->roomModel = new RoomModel();
    }

    public function index()
    {
        return $this->renderView('rooms/index', [
            'title'      => 'Master Ruangan',
            'page_title' => 'Master Ruangan',
            'rooms'      => $this->roomModel->orderBy('code', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return $this->renderView('rooms/create', [
            'title'      => 'Tambah Ruangan',
            'page_title' => 'Tambah Ruangan',
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->roomModel->insert($this->roomData());

        return redirect()->to('/admin/rooms')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $room = $this->roomModel->find($id);

        if (! $room) {
            return redirect()->to('/admin/rooms')->with('error', 'Ruangan tidak ditemukan.');
        }

        return $this->renderView('rooms/edit', [
            'title'      => 'Edit Ruangan',
            'page_title' => 'Edit Ruangan',
            'room'       => $room,
        ]);
    }

    public function update(int $id)
    {
        if (! $this->roomModel->find($id)) {
            return redirect()->to('/admin/rooms')->with('error', 'Ruangan tidak ditemukan.');
        }

        if (! $this->validate($this->validationRules($id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->roomModel->update($id, $this->roomData());

        return redirect()->to('/admin/rooms')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (! $this->roomModel->find($id)) {
            return redirect()->to('/admin/rooms')->with('error', 'Ruangan tidak ditemukan.');
        }

        $this->roomModel->delete($id);

        return redirect()->to('/admin/rooms')->with('success', 'Ruangan berhasil dihapus.');
    }

    private function validationRules(?int $id = null): array
    {
        $uniqueCode = $id === null ? 'is_unique[rooms.code]' : "is_unique[rooms.code,id,{$id}]";

        return [
            'code'        => "required|alpha_numeric_punct|min_length[2]|max_length[30]|{$uniqueCode}",
            'name'        => 'required|min_length[3]|max_length[100]',
            'building'    => 'permit_empty|max_length[100]',
            'floor'       => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
            'capacity'    => 'required|integer|greater_than[0]|less_than_equal_to[10000]',
            'type'        => 'required|in_list[laboratorium,kelas,meeting,gudang,lainnya]',
            'status'      => 'required|in_list[active,inactive]',
            'description' => 'permit_empty|max_length[500]',
        ];
    }

    private function roomData(): array
    {
        return [
            'code'        => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'        => trim((string) $this->request->getPost('name')),
            'building'    => trim((string) $this->request->getPost('building')),
            'floor'       => $this->request->getPost('floor') === '' ? null : (int) $this->request->getPost('floor'),
            'capacity'    => (int) $this->request->getPost('capacity'),
            'type'        => $this->request->getPost('type'),
            'status'      => $this->request->getPost('status'),
            'description' => trim((string) $this->request->getPost('description')),
        ];
    }
}