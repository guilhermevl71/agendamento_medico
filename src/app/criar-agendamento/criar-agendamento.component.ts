import { Component, OnInit } from '@angular/core';
import { DoctorService } from '../services/doctor.service';
import { AppointmentService } from '../services/appointment.service';
import { NgFor, NgIf } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Route, Router } from '@angular/router';

@Component({
  selector: 'app-criar-agendamento',
  imports: [NgIf, NgFor, FormsModule],
  templateUrl: './criar-agendamento.component.html',
  styleUrls: ['./criar-agendamento.component.css']
})
export class CriarAgendamentoComponent implements OnInit {

  doctors: any[] = [];
  selectedDoctorId: number | null = null;

  selectedDoctorData: any = null;
  selectedTime: any = null;

  // Seleção de dia
  selectedDay: string | null = null;
  filteredTimes: any[] = [];

  // controle de telas
  telaSelecionar = true;
  telaConfirmar = false;

  constructor(
    private doctorService: DoctorService,
    private appointmentService: AppointmentService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadDoctors();
  }

  loadDoctors() {
    this.doctorService.getDoctors().subscribe(res => {
      this.doctors = res;
    });
  }

  prosseguir() {
    if (!this.selectedDoctorId) return;

    this.doctorService.getDoctorDetails(this.selectedDoctorId).subscribe(res => {
      this.selectedDoctorData = res;

      // Inicializa a lista de horários
      this.filteredTimes = this.selectedDoctorData.horarios || [];
      this.selectedTime = null;
      this.selectedDay = null;

      this.telaSelecionar = false;
      this.telaConfirmar = true;
    });
  }

  // Atualiza horários filtrando pelo dia selecionado
  updateAvailableTimes() {
  if (!this.selectedDay) {
    this.filteredTimes = this.selectedDoctorData.horarios;
    return;
  }
  this.filteredTimes = this.selectedDoctorData.horarios.filter((h: { weekday: string; start_time: string; end_time: string }) => h.weekday === this.selectedDay);
  this.selectedTime = null; // limpa horário selecionado
  }

  selectTime(h: any) {
    this.selectedTime = h;
  }

  createAppointment() {
    if (!this.selectedTime) return;

    const body = {
      user_id: Number(sessionStorage.getItem('user_id')),
      doctor_id: this.selectedDoctorId,
      date: this.selectedTime.date || new Date().toISOString().split('T')[0],
      time: this.selectedTime.start_time
    };

    this.appointmentService.createAppointment(body).subscribe(() => {
      alert("Agendamento criado com sucesso!");

      // Reset das telas
      this.telaSelecionar = true;
      this.telaConfirmar = false;
      this.selectedDoctorId = null;
      this.selectedTime = null;
      this.selectedDay = null;
      this.filteredTimes = [];

      this.router.navigate(['/dashboard']);
    });
  }
}
