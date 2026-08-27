<?php

namespace App\Controllers;

use App\Models\RoomModel;

class RoomController extends BaseController
{
    protected RoomModel $roomModel;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct()
    {
        $this->roomModel = new RoomModel();
    }

    public function index()
    {
        $search  = trim((string) $this->request->getGet('q'));
        $type    = (string) $this->request->getGet('type');
        $status  = (string) $this->request->getGet('status');
        $perPage = (int) $this->request->getGet('perPage');

        $types = ['laboratorium', 'kelas', 'meeting', 'gudang', 'lainnya'];

        if (! in_array($type, $types, true)) {
            $type = '';
        }

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->roomModel->orderBy('code', 'ASC');

        if ($search !== '') {
            $query->groupStart()
                ->like('code', $search)
                ->orLike('name', $search)
                ->orLike('building', $search)
                ->groupEnd();
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $rooms = $query->paginate($perPage);
        $pager = $this->roomModel->pager;

        return $this->renderView('rooms/index', [
            'title'         => 'Master Ruangan',
            'page_title'    => 'Master Ruangan',
            'rooms'         => $rooms,
            'pager'         => $pager,
            'search'        => $search,
            'type'          => $type,
            'status'        => $status,
            'perPage'       => $perPage,
            'perPageOptions'=> self::PER_PAGE_OPTIONS,
            'currentPage'   => $pager->getCurrentPage(),
            'totalRows'     => $pager->getTotal(),
            'types'         => $types,
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