class Disc < ActiveRecord::Base
  @@statuses = {
    1 => :not_dumped,
    2 => :dumped,
    3 => :redumped
  }
  
  def self.statuses
    @@statuses
  end
  
  validates_inclusion_of :status, :in => @@statuses.keys
end
