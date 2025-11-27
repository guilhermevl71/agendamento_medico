import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { CriarAgendamentoComponent } from './criar-agendamento/criar-agendamento.component';
import { loggedGuard } from './guards/logged.guard';
import { authGuard } from './guards/auth.guard';

export const routes: Routes = [
    {path: '', redirectTo: 'login', pathMatch: 'full'},
    {path: 'login', component: LoginComponent, canActivate: [loggedGuard]},
    {path: 'dashboard', component: DashboardComponent, canActivate: [authGuard]},
    {path: 'criar-agendamento', component: CriarAgendamentoComponent, canActivate: [authGuard]}
];
