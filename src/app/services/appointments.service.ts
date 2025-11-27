import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AppointmentsService {

  private apiUrl = 'http://localhost:8000/api/getAppointments.php';

  constructor(private http: HttpClient) { }

  getAppointments(): Observable<any> {
    return this.http.get(this.apiUrl);
  }

}
