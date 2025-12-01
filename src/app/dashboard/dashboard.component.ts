import { Component, OnInit } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router, RouterLink } from '@angular/router';
import { AppointmentsService } from '../services/appointments.service';
import { NgFor, NgIf } from '@angular/common';

@Component({
  selector: 'app-dashboard',
  imports: [NgFor, RouterLink],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent implements OnInit {

   appointments: any[] = [];

  constructor(private auth: AuthService, private router: Router, private appointService: AppointmentsService) { }

  ngOnInit(): void {
    this.appointService.getAppointments().subscribe({
      next: (res) => {
        this.appointments = res.appointments;
      },
      error: () => {
        console.error("Erro ao carregar agendamentos");
      }
    });
  }

  logout() {
    sessionStorage.removeItem('token');
    window.location.href = '/login';
  }

}
