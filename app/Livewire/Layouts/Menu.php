<?php

namespace App\Livewire\Layouts;

use App\Enums\EnrollmentStatus;
use App\Enums\Feature;
use App\Enums\PortalArea;
use App\Models\AccountInvitation;
use App\Models\CalendarEvent;
use App\Models\CampusMoveRequest;
use App\Models\DataSharingRequest;
use App\Models\Incident;
use App\Models\Organization;
use App\Models\School;
use App\Models\StaffLeaveRequest;
use App\Models\StaffProfile;
use App\Models\StudentHealthRecord;
use App\Models\StudentRecord;
use App\Models\SupportPlan;
use App\Models\User;
use App\Services\Portal\PortalAccess;
use App\Services\School\SchoolService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Menu extends Component
{
  private const PortalEnrollmentKey = 'portal.sidebar_enrollment_id';

  /**
   * @var Collection<int, School>
   */
  #[Locked]
  public Collection $schools;

  /**
   * @var list<array<string, mixed>>
   */
  #[Locked]
  public array $menu = [];

  /**
   * @var list<array{key: string, label: string, group: string, url: string, keywords: string}>
   */
  #[Locked]
  public array $commandItems = [];

  public function mount(SchoolService $schoolService): void
  {
    $user = auth()->user();
    $this->schools = $schoolService->getSchoolsForUser($user);

    // A person can hold organization authority without a working school,
    // so the sidebar must render before any school is chosen.
    $organization = current_school()?->organization;
    $portalEnrollment = $this->portalEnrollment($user);
    $readsSeveralEnrollments = app(PortalAccess::class)->enrollmentsFor($user)->count() > 1;
    $this->menu = [
      [
        'type' => 'menu-item',
        'icon' => 'gauge',
        'text' => 'Dashboard',
        'route' => 'dashboard',
      ],
      ...($portalEnrollment === null ? [] : [
        ['header' => $readsSeveralEnrollments && $portalEnrollment->user !== null && !$portalEnrollment->user->is($user) ? $portalEnrollment->user->name : 'My school'],
        ...$this->portalAreaItems($portalEnrollment),
        ...(!$readsSeveralEnrollments ? [] : [[
          'type' => 'menu-item',
          'icon' => 'layout-list',
          'text' => 'Everything of mine',
          'route' => 'portal.overview',
        ]]),
        [
          'type' => 'menu-item',
          'icon' => 'bell',
          'text' => 'Notification settings',
          'route' => 'portal.notification-preferences.edit',
        ],
      ]),
      ['header' => 'Organization'],
      [
        'type' => 'menu-item',
        'icon' => 'building-2',
        'text' => 'Organizaciones',
        'route' => 'organizations.index',
        'visible' => $user->can('viewAny', Organization::class),
      ],
      [
        'type' => 'menu-item',
        'icon' => 'school',
        'text' => 'Escuelas',
        'route' => 'schools.index',
        'can' => 'read school',
      ],
      ...($organization === null ? [] : [[
        'type' => 'menu-item',
        'icon' => 'calendar-range',
        'text' => 'Plantillas de calendario',
        'route' => 'organizations.calendar-templates.index',
        'parameters' => [$organization],
        'visible' => $user->can('view', $organization),
      ]]),
      ['header' => 'Setup'],
      [
        'type' => 'menu-item',
        'icon' => 'settings',
        'text' => 'Configuración de la escuela',
        'route' => 'schools.settings',
        'can' => 'manage school settings',
      ],
      [
        'type' => 'menu-item',
        'icon' => 'calendar',
        'text' => school_terms('academic_year', 'Años escolares'),
        'route' => 'academic-years.index',
        'can' => 'read academic year',
      ],
      [
        'type' => 'menu-item',
        'icon' => 'presentation',
        'text' => school_terms('class_level', 'Clases'),
        'route' => 'academic-levels.index',
        'can' => 'read class',
      ],
      [
        'type' => 'menu-item',
        'icon' => 'landmark',
        'text' => school_terms('section', 'Secciones'),
        'route' => 'academic-cycle-sections.index',
        'can' => 'read section',
      ],
      ['header' => 'People'],
      [
        'type' => 'menu-item',
        'text' => 'Estudiantes',
        'icon' => 'user',
        'route' => 'students.index',
        'visible' => !$user->isPortalOnly() && $user->can('read student'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Lista de espera de admisiones',
        'icon' => 'list-ordered',
        'route' => 'admissions.waitlist.index',
        'can' => 'read admission waitlist',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Compartir registros',
        'icon' => 'share-2',
        'route' => 'data-sharing-requests.index',
        'visible' => $user->can('viewAny', DataSharingRequest::class),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Movimientos de campus',
        'icon' => 'building-2',
        'route' => 'campus-moves.index',
        'visible' => $user->can('viewAny', CampusMoveRequest::class),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Profesores',
        'icon' => 'graduation-cap',
        'route' => 'teachers.index',
        'can' => 'read teacher',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Padres',
        'icon' => 'users',
        'route' => 'parents.index',
        'can' => 'read parent',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Grupos',
        'icon' => 'users-round',
        'route' => 'cohorts.index',
        'can' => 'read cohort',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Planes de graduación',
        'icon' => 'graduation-cap',
        'route' => 'graduation-plans.index',
        'can' => 'read graduation plan',
        'visible' => feature_enabled(Feature::GraduationPlans) && $user->can('read graduation plan'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Promociones',
        'icon' => 'circle-arrow-up',
        'route' => 'students.promotions',
        'can' => 'read promotion',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Graduaciones',
        'icon' => 'award',
        'route' => 'students.graduations',
        'can' => 'view graduations',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Programas',
        'icon' => 'sparkles',
        'route' => 'programs.index',
        'can' => 'read program',
        'visible' => feature_enabled(Feature::Programmes) && $user->can('read program'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Administradores',
        'icon' => 'shield-check',
        'route' => 'admins.index',
        'can' => 'read admin',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Invitaciones de cuenta',
        'icon' => 'mail',
        'route' => 'users.invitations.index',
        'visible' => $user->can('viewAny', AccountInvitation::class),
      ],
      ['header' => 'Teaching'],
      [
        'type' => 'menu-item',
        'text' => 'Materias',
        'icon' => 'lightbulb',
        'route' => 'subjects.index',
        'can' => 'read subject',
      ],
      [
        'type' => 'menu-item',
        'text' => school_terms('course', 'Cursos') . ' de formación',
        'icon' => 'book-marked',
        'route' => 'course-offerings.index',
        'can' => 'read subject',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Programas de estudio',
        'icon' => 'list',
        'route' => 'syllabi.index',
        'visible' => !$user->isParentPortalOnly() && $user->can('read syllabus'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Horarios',
        'icon' => 'list-checks',
        'route' => 'timetables.index',
        'visible' => !$user->isParentPortalOnly() && $user->can('read timetable'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Asistencia',
        'icon' => 'clipboard-check',
        'route' => 'attendance.register',
        'visible' => feature_enabled(Feature::Attendance) && !$user->isParentPortalOnly() && $user->can('read attendance'),
      ],
      ['header' => 'Evaluación'],
      [
        'type' => 'menu-item',
        'text' => 'Libretas de calificaciones',
        'icon' => 'clipboard-pen-line',
        'route' => 'gradebooks.index',
        'visible' => $user->can('menu-gradebook') && $user->can('read gradebook'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Horarios de exámenes',
        'icon' => 'calendar-days',
        'route' => 'exams.index',
        'can' => 'read exam',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Rankings',
        'icon' => 'list-ordered',
        'route' => 'rankings.index',
        'visible' => feature_enabled(Feature::Ranking) && $user->can('read ranking'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Escala de calificación',
        'icon' => 'graduation-cap',
        'route' => 'grading-scales.index',
        'can' => 'manage grading scale',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Boletines de calificaciones',
        'icon' => 'file-badge',
        'route' => 'report-cards.index',
        'can' => 'read report',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Transcripciones',
        'icon' => 'scroll-text',
        'route' => 'transcripts.index',
        'can' => 'read report',
      ],
      ['header' => 'Operations'],
      [
        'type' => 'menu-item',
        'text' => 'Reportes',
        'icon' => 'file-text',
        'route' => 'reports.index',
        'can' => 'read report',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Solicitudes de familia',
        'icon' => 'message-square',
        'route' => 'portal-requests.index',
        'can' => 'read portal request',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Calendario de eventos',
        'icon' => 'calendar-days',
        'route' => 'calendar-events.index',
        'visible' => !$user->isPortalOnly()
          && feature_enabled(Feature::Events)
          && $user->can('viewAny', CalendarEvent::class),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Noticias',
        'icon' => 'bell',
        'route' => 'notices.index',
        'visible' => !$user->isPortalOnly() && $user->can('read notice'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Casos',
        'icon' => 'shield-alert',
        'route' => 'incidents.index',
        'visible' => feature_enabled(Feature::Discipline) && $user->can('viewAny', Incident::class),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Soporte de estudiantes',
        'icon' => 'heart-handshake',
        'submenu' => [
          [
            'text' => 'Planes de soporte',
            'icon' => 'heart-handshake',
            'route' => 'support-plans.index',
            'visible' => feature_enabled(Feature::Wellbeing) && $user->can('viewAny', SupportPlan::class),
          ],
          [
            'text' => 'Records de salud',
            'icon' => 'heart-pulse',
            'route' => 'health-records.index',
            'visible' => feature_enabled(Feature::Wellbeing) && $user->can('viewAny', StudentHealthRecord::class),
          ],
        ],
      ],
      [
        'type' => 'menu-item',
        'text' => 'Staff',
        'icon' => 'briefcase',
        'submenu' => [
          [
            'text' => 'Directorio del personal',
            'icon' => 'briefcase',
            'route' => 'staff-profiles.index',
            'visible' => feature_enabled(Feature::StaffOperations) && $user->can('viewAny', StaffProfile::class),
          ],
          [
            'text' => 'Permisos del personal',
            'icon' => 'plane',
            'route' => 'staff-leave.index',
            'visible' => feature_enabled(Feature::StaffOperations) && $user->can('viewAny', StaffLeaveRequest::class),
          ],
        ],
      ],
      [
        'type' => 'menu-item',
        'text' => 'Finance',
        'icon' => 'dollar-sign',
        'submenu' => [
          [
            'text' => 'Facturas de matrícula',
            'icon' => 'receipt',
            'route' => 'fee-invoices.index',
            'visible' => !$user->isPortalOnly() && $user->can('read fee invoice'),
          ],
          [
            'text' => 'Presupuestos',
            'icon' => 'wallet',
            'route' => 'budgets.index',
            'visible' => !$user->isPortalOnly() && $user->can('read budget'),
          ],
        ],
      ],
      [
        'type' => 'menu-item',
        'text' => 'Boarding',
        'icon' => 'bed-double',
        'submenu' => [
          [
            'text' => 'Dormitorios',
            'icon' => 'house',
            'route' => 'dormitories.index',
            'visible' => feature_enabled(Feature::Boarding) && $user->can('read boarding'),
          ],
          [
            'text' => 'Noches fuera',
            'icon' => 'moon',
            'route' => 'overnight-leaves.index',
            'visible' => feature_enabled(Feature::Boarding) && $user->can('read boarding'),
          ],
        ],
      ],
      [
        'type' => 'menu-item',
        'text' => 'Instalaciones',
        'icon' => 'building-2',
        'route' => 'facilities.index',
        'can' => 'read facility',
      ],
      [
        'type' => 'menu-item',
        'text' => 'Libreria',
        'icon' => 'library',
        'submenu' => [
          [
            'text' => 'Catalogo de libros',
            'icon' => 'library',
            'route' => 'library-copies.index',
            'visible' => feature_enabled(Feature::Library) && $user->can('read library'),
          ],
          [
            'text' => 'Prestamos de libros',
            'icon' => 'book-open-check',
            'route' => 'library-loans.index',
            'visible' => feature_enabled(Feature::Library) && $user->can('read library'),
          ],
          [
            'text' => 'Cola de la libreria',
            'icon' => 'clock',
            'route' => 'library-reservations.index',
            'visible' => feature_enabled(Feature::Library) && $user->can('read library'),
          ],
        ],
      ],
      [
        'type' => 'menu-item',
        'text' => 'Importes',
        'icon' => 'upload',
        'route' => 'imports.index',
        'visible' => feature_enabled(Feature::Imports) && $user->can('read import'),
      ],
      [
        'type' => 'menu-item',
        'text' => 'Roles',
        'icon' => 'shield-check',
        'route' => 'roles.index',
        'can' => 'read role',
      ],
      ['header' => 'System'],
      [
        'type' => 'menu-item',
        'text' => 'Registro de actividades',
        'route' => 'log-viewer.index',
        'icon' => 'sticky-note',
        'can' => 'viewLogViewer',
      ],
    ];

    $this->menu = $this->withVisibility($this->menu);
    $this->commandItems = $this->buildCommandPaletteItems($this->menu);
  }

  /**
   * Rebuild the sidebar after the school turns a tool on or off.
   */
  #[On('school-features-changed')]
  public function rebuild(SchoolService $schoolService): void
  {
    $this->mount($schoolService);
  }

  /**
   * Work in another school the person belongs to.
   */
  public function switchSchool(int $schoolId, SchoolService $schoolService): void
  {
    Gate::authorize('setSchool', School::class);

    $school = School::query()->find($schoolId);
    abort_if($school === null, 404);

    $schoolService->setSchool($school);

    session()->flash('success', __('School set successfully'));
    $this->redirectRoute('dashboard');
  }

  public function render()
  {
    return view('livewire.layouts.menu');
  }

  /**
   * Build route entries from the same permission-filtered tree as the sidebar.
   *
   * @param  list<array<string, mixed>>  $menu
   * @return list<array{key: string, label: string, group: string, url: string, keywords: string}>
   */
  private function buildCommandPaletteItems(array $menu, string $group = 'Workspace'): array
  {
    $items = [];
    $group = __($group);
    $schoolTerms = [
      school_term('academic_year', 'School year'),
      school_terms('academic_year', 'School years'),
      school_term('class_level', 'Class'),
      school_terms('class_level', 'Classes'),
      school_term('section', 'Section'),
      school_terms('section', 'Sections'),
      school_term('period', 'Academic period'),
      school_terms('period', 'Academic periods'),
      school_term('course', 'Course offering'),
      school_terms('course', 'Course offerings'),
    ];

    foreach ($menu as $menuItem) {
      if (isset($menuItem['header'])) {
        $group = __($menuItem['header']);

        continue;
      }

      if (!($menuItem['visible'] ?? true)) {
        continue;
      }

      if (isset($menuItem['submenu'])) {
        $items = [
          ...$items,
          ...$this->buildCommandPaletteItems($menuItem['submenu'], $group),
        ];

        continue;
      }

      if (!isset($menuItem['route'], $menuItem['text'])) {
        continue;
      }

      $label = __($menuItem['text']);
      $routeName = (string) $menuItem['route'];

      $items[] = [
        'key' => $routeName . '-' . count($items),
        'label' => $label,
        'group' => $group,
        'url' => route($routeName, $menuItem['parameters'] ?? []),
        'keywords' => implode(' ', [
          $label,
          $group,
          str_replace(['.', '-', '_'], ' ', $routeName),
          ...$schoolTerms,
        ]),
      ];
    }

    return $items;
  }

  /**
   * Choose the enrollment whose pages the sidebar lists.
   *
   * The enrollment on the open portal page wins, so a parent who opens a
   * second child's page sees that child's links. The session keeps the
   * choice for the next page. Otherwise a current enrollment comes first.
   */
  private function portalEnrollment(User $user): ?StudentRecord
  {
    $access = app(PortalAccess::class);
    $enrollments = $access->enrollmentsFor($user)
      ->filter(fn(StudentRecord $enrollment): bool => $access->isOpen($enrollment->school_id));

    $onPage = request()->route('studentRecord');
    $chosenId = $onPage instanceof StudentRecord ? $onPage->id : session(self::PortalEnrollmentKey);

    /** @var StudentRecord|null $enrollment */
    $enrollment = $enrollments->firstWhere('id', $chosenId)
      ?? $enrollments->first(fn(StudentRecord $enrollment): bool => in_array($enrollment->status, EnrollmentStatus::enrolled(), true))
      ?? $enrollments->last();

    if ($enrollment !== null) {
      session([self::PortalEnrollmentKey => $enrollment->id]);
    }

    return $enrollment;
  }

  /**
   * Link each open portal page of the chosen enrollment.
   *
   * @return list<array<string, mixed>>
   */
  private function portalAreaItems(?StudentRecord $enrollment): array
  {
    if ($enrollment === null) {
      return [];
    }

    return array_map(fn(PortalArea $area): array => [
      'type' => 'menu-item',
      'icon' => $area->icon(),
      'text' => $area->label(),
      'route' => $area->routeName(),
      'parameters' => [$enrollment],
    ], app(PortalAccess::class)->openAreas($enrollment));
  }

  /**
   * Mark each item according to its own permission and its visible children.
   *
   * @param  list<array<string, mixed>>  $menu
   * @return list<array<string, mixed>>
   */
  private function withVisibility(array $menu): array
  {
    return array_map(function (array $menuItem): array {
      if (isset($menuItem['header'])) {
        return $menuItem;
      }

      if (isset($menuItem['submenu'])) {
        $menuItem['submenu'] = $this->withVisibility($menuItem['submenu']);
      }

      $hasVisibleSubmenu = !isset($menuItem['submenu'])
        || collect($menuItem['submenu'])->contains(
          fn(array $submenu): bool => $submenu['visible'] ?? true,
        );

      $menuItem['visible'] = ($menuItem['visible'] ?? true)
        && (!isset($menuItem['can']) || auth()->user()->can($menuItem['can']))
        && $hasVisibleSubmenu;

      return $menuItem;
    }, $menu);
  }
}
